<?php

namespace Tests\Unit;

use App\Services\BiometricRecordParser;
use PHPUnit\Framework\TestCase;

class BiometricRecordParserTest extends TestCase
{
    private function parser(): BiometricRecordParser
    {
        return new BiometricRecordParser();
    }

    public function test_it_parses_a_legacy_fp_record(): void
    {
        $payload = "FP PIN=1234\tFID=2\tSize=1404\tValid=1\tTMP=QUJDREVGRw==\r\n";

        $records = $this->parser()->parseFingerprints($payload);

        $this->assertCount(1, $records);
        $this->assertSame('1234', $records[0]['pin']);
        $this->assertSame(2, $records[0]['fid']);
        $this->assertSame(1404, $records[0]['size']);
        $this->assertSame(1, $records[0]['valid']);
        $this->assertSame('QUJDREVGRw==', $records[0]['template']);
    }

    public function test_it_parses_a_biodata_fingerprint_record(): void
    {
        $payload = "BIODATA Pin=77\tNo=3\tIndex=0\tValid=1\tDuress=0\tType=1"
            . "\tMajorVer=39\tMinorVer=39\tFormat=0\tTmp=VEVNUExBVEU=";

        $records = $this->parser()->parseFingerprints($payload);

        $this->assertCount(1, $records);
        $this->assertSame('77', $records[0]['pin']);
        $this->assertSame(3, $records[0]['fid']);
        $this->assertSame('VEVNUExBVEU=', $records[0]['template']);
        $this->assertSame('39.39', $records[0]['version']);
        // No Size field on this firmware — derived from the payload.
        $this->assertSame(strlen('VEVNUExBVEU='), $records[0]['size']);
    }

    public function test_it_does_not_treat_a_face_template_as_a_fingerprint(): void
    {
        $payload = "BIODATA Pin=77\tNo=0\tValid=1\tType=2\tTmp=RkFDRQ==";

        $this->assertSame([], $this->parser()->parseFingerprints($payload));

        $records = $this->parser()->parse($payload);
        $this->assertSame('face', $records[0]['kind']);
        $this->assertSame('RkFDRQ==', $records[0]['template']);
        $this->assertCount(1, $this->parser()->parseFaces($payload));
    }

    public function test_it_parses_a_legacy_face_record(): void
    {
        $payload = "FACE PIN=5\tFID=50\tSIZE=1704\tVALID=1\tTMP=RkFDRQ==";

        $records = $this->parser()->parseFaces($payload);

        $this->assertCount(1, $records);
        $this->assertSame('5', $records[0]['pin']);
        $this->assertSame(50, $records[0]['fid']);
        $this->assertSame([], $this->parser()->parseFingerprints($payload));
    }

    public function test_a_face_record_carries_its_biometric_type(): void
    {
        $parser = $this->parser();

        $this->assertSame(9, $parser->parseFaces("BIODATA Pin=7\tNo=0\tValid=1\tType=9\tTmp=RkFDRQ==")[0]['bio_type']);
        // Legacy FACE lines have no Type and only come from near-infrared terminals.
        $this->assertSame(2, $parser->parseFaces("FACE PIN=5\tFID=3\tSIZE=4\tVALID=1\tTMP=RkFDRQ==")[0]['bio_type']);
    }

    public function test_face_templates_sharing_a_number_keep_distinct_slots(): void
    {
        $payload = "BIODATA Pin=7\tNo=0\tIndex=0\tValid=1\tType=2\tTmp=QQ==\n"
            . "BIODATA Pin=7\tNo=0\tIndex=5\tValid=1\tType=2\tTmp=Qg==";

        $records = $this->parser()->parseFaces($payload);

        $this->assertSame([0, 5], array_column($records, 'fid'));
    }

    public function test_it_parses_a_face_comparison_photo(): void
    {
        $payload = "BIOPHOTO PIN=12\tFileName=12.jpg\tType=9\tSize=16\tContent=/9j/4AAQSkZJRg==";

        $this->assertTrue($this->parser()->looksLikeBiometricPayload($payload));

        $records = $this->parser()->parseFacePhotos($payload);

        $this->assertCount(1, $records);
        $this->assertSame('12', $records[0]['pin']);
        $this->assertSame(9, $records[0]['bio_type']);
        $this->assertSame('12.jpg', $records[0]['file_name']);
        $this->assertSame(16, $records[0]['size']);
        // Base64 padding must survive the key=value split.
        $this->assertSame('/9j/4AAQSkZJRg==', $records[0]['content']);
        $this->assertSame([], $this->parser()->parseFaces($payload));
        $this->assertSame([], $this->parser()->parseFingerprints($payload));
    }

    public function test_other_biometric_types_are_not_faces(): void
    {
        $payload = "BIODATA Pin=77\tNo=0\tValid=1\tType=8\tTmp=UEFMTQ==";

        $this->assertSame([], $this->parser()->parseFaces($payload));
        $this->assertSame('biometric', $this->parser()->parse($payload)[0]['kind']);
    }

    public function test_it_keeps_names_containing_spaces_intact(): void
    {
        // Space-separated firmware: the value of Name= must not be split.
        $payload = 'USER PIN=9 Name=Ana Maria Lopez Pri=0 Passwd= Card= Grp=1';

        $records = $this->parser()->parse($payload);

        $this->assertSame('user', $records[0]['kind']);
        $this->assertSame('9', $records[0]['pin']);
        $this->assertSame('Ana Maria Lopez', $records[0]['name']);
    }

    public function test_it_parses_several_records_from_one_payload(): void
    {
        $payload = implode("\r\n", [
            "USER PIN=1\tName=Test User\tPri=0",
            "FP PIN=1\tFID=0\tSize=100\tValid=1\tTMP=QQ==",
            "FP PIN=1\tFID=1\tSize=100\tValid=1\tTMP=Qg==",
            '',
        ]);

        $records = $this->parser()->parse($payload);

        $this->assertCount(3, $records);
        $this->assertCount(2, $this->parser()->parseFingerprints($payload));
    }

    public function test_it_recognises_a_biometric_payload(): void
    {
        $parser = $this->parser();

        $this->assertTrue($parser->looksLikeBiometricPayload("FP PIN=1\tTMP=QQ=="));
        $this->assertTrue($parser->looksLikeBiometricPayload("USER PIN=1\tName=x"));
        $this->assertFalse($parser->looksLikeBiometricPayload("1\t2025-01-01 08:00:00\t1\t1"));
        $this->assertFalse($parser->looksLikeBiometricPayload(''));
    }

    public function test_it_ignores_a_template_free_line(): void
    {
        $this->assertSame([], $this->parser()->parseFingerprints("FP PIN=1\tFID=0\tSize=0\tValid=1\tTMP="));
    }

    public function test_it_parses_command_acknowledgements(): void
    {
        $acks = BiometricRecordParser::parseCommandAcks("ID=42&Return=0&CMD=DATA\r\nID=43&Return=-1&CMD=CHECK");

        $this->assertCount(2, $acks);
        $this->assertSame(42, $acks[0]['id']);
        $this->assertSame(0, $acks[0]['return']);
        $this->assertSame('DATA', $acks[0]['cmd']);
        $this->assertSame(-1, $acks[1]['return']);
    }

    public function test_it_ignores_an_unparsable_acknowledgement(): void
    {
        $this->assertSame([], BiometricRecordParser::parseCommandAcks('nonsense'));
        $this->assertSame([], BiometricRecordParser::parseCommandAcks(null));
    }
}
