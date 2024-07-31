<?php

namespace App\Http\Controllers;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class iclockController extends Controller
{

   public function __invoke(Request $request)
   {

   }

    // handshake
public function handshake(Request $request)
{
    $data = [
        'url' => json_encode($request->all()),
        'data' => $request->getContent(),
        'sn' => $request->input('SN'),
        'option' => $request->input('option'),
    ];
    DB::table('device_log')->insert($data);

    // update status device
    DB::table('devices')->updateOrInsert(
        ['no_sn' => $request->input('SN')],
        ['online' => now()]
    );

    $r = "GET OPTION FROM: {$request->input('SN')}\r\n" .
         "Stamp=9999\r\n" .
         "OpStamp=" . time() . "\r\n" .
         "ErrorDelay=60\r\n" .
         "Delay=30\r\n" .
         "ResLogDay=18250\r\n" .
         "ResLogDelCount=10000\r\n" .
         "ResLogCount=50000\r\n" .
         "TransTimes=00:00;14:05\r\n" .
         "TransInterval=1\r\n" .
         "TransFlag=1111000000\r\n" .
        //  "TimeZone=7\r\n" .
         "Realtime=1\r\n" .
         "Encrypt=0";

    return $r;
}
        //$r = "GET OPTION FROM:%s{$request->SN}\nStamp=".strtotime('now')."\nOpStamp=1565089939\nErrorDelay=30\nDelay=10\nTransTimes=00:00;14:05\nTransInterval=1\nTransFlag=1111000000\nTimeZone=7\nRealtime=1\nEncrypt=0\n";
    // implementasi https://docs.nufaza.com/docs/devices/zkteco_attendance/push_protocol/
    // setting timezone
    // request absensi
    public function receiveRecords(Request $request)
    {   
        
        //DB::connection()->enableQueryLog();
        $content['url'] = json_encode($request->all());
        $content['data'] = $request->getContent();;
        DB::table('finger_log')->insert($content);
        try {
            // $post_content = $request->getContent();
            //$arr = explode("\n", $post_content);
            $arr = preg_split('/\\r\\n|\\r|,|\\n/', $request->getContent());
            //$tot = count($arr);
            $tot = 0;
            //operation log
            if($request->input('table') == "OPERLOG"){
                // $tot = count($arr) - 1;
                foreach ($arr as $rey) {
                    if(isset($rey)){
                        $tot++;
                    }
                }
                return "OK: ".$tot;
            }
            //attendance
            foreach ($arr as $rey) {
                // $data = preg_split('/\s+/', trim($rey));
                if(empty($rey)){
                    continue;
                }
                    // $data = preg_split('/\s+/', trim($rey));
                    $data = explode("\t",$rey);
                    //dd($data);
                    $q['sn'] = $request->input('SN');
                    $q['table'] = $request->input('table');
                    $q['stamp'] = $request->input('Stamp');
                    $q['employee_id'] = $data[0];
                    $q['timestamp'] = $data[1];
                    $q['status1'] = $this->validateAndFormatInteger($data[2] ?? null);
                    $q['status2'] = $this->validateAndFormatInteger($data[3] ?? null);
                    $q['status3'] = $this->validateAndFormatInteger($data[4] ?? null);
                    $q['status4'] = $this->validateAndFormatInteger($data[5] ?? null);
                    $q['status5'] = $this->validateAndFormatInteger($data[6] ?? null);
                    $q['created_at'] = now();
                    $q['updated_at'] = now();
                    //dd($q);
                    DB::table('attendances')->insert($q);
                    $tot++;
                // dd(DB::getQueryLog());
            }
            return "OK: ".$tot;
        } catch (Throwable $e) {
            $data['error'] = $e;
            DB::table('error_log')->insert($data);
            report($e);
            return "ERROR: ".$tot."\n";
        }
    }
    public function test(Request $request)
    {
                $log['data'] = $request->getContent();
                DB::table('finger_log')->insert($log);
    }
    public function getrequest(Request $request)
    {
        // $r = "GET OPTION FROM: ".$request->SN."\nStamp=".strtotime('now')."\nOpStamp=".strtotime('now')."\nErrorDelay=60\nDelay=30\nResLogDay=18250\nResLogDelCount=10000\nResLogCount=50000\nTransTimes=00:00;14:05\nTransInterval=1\nTransFlag=1111000000\nRealtime=1\nEncrypt=0";

        return "OK";
    }
    private function validateAndFormatInteger($value)
    {
        return isset($value) && $value !== '' ? (int)$value : null;
        // return is_numeric($value) ? (int) $value : null;
    }

}
