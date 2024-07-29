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
        $data['url'] = $request->all();
        $data["data"] = json_encode($request->getContent());
        $data["sn"] = $request->SN;
        $data["option"] = $request->option;
        DB::table('device_log')->insert($data);

        // update status device
        DB::table('devices')->updateOrInsert(
            ['no_sn' => $request->SN],
            ['online' => now()]
        );
            // $r = "GET OPTION FROM: ".$request->SN." \n\r
            // Stamp=9999 \n\r
            // OpStamp=".strtotime('now')." \n\r
            // ErrorDelay=60 \n\r
            // Delay=30 \n\r
            // ResLogDay=18250 \n\r
            // ResLogDelCount=10000 \n\r
            // ResLogCount=50000 \n\r
            // TransTimes=00:00;14:05 \n\r
            // TransInterval=1 \n\r
            // TransFlag=1111000000 \n\r
            // Realtime=1 \n\r
            // Encrypt=0";
            // $r = trim(preg_replace('/\t+/', '', $r));
        $r = "GET OPTION FROM:%s{$request->SN}\nStamp=".strtotime('now')."\nOpStamp=1565089939\nErrorDelay=30\nDelay=10\nTransTimes=00:00;14:05\nTransInterval=1\nTransFlag=1111000000\nTimeZone=7\nRealtime=1\nEncrypt=0\n";

        return $r;
    }
    // implementasi https://docs.nufaza.com/docs/devices/zkteco_attendance/push_protocol/
    // setting timezone

    // request absensi
    public function receiveRecords(Request $request)
    {   
        
        try {
        $content['url'] = json_encode($request->all());
        $content['data'] = json_encode($request->getContent());;
        DB::table('finger_log')->insert($content);
        //$arr = preg_split('/\\r\\n|\\r|,|\\n/', $content['data']);
        $arr = explode("\n", $content['data']);
        $tot = count($arr);

        foreach ($arr as $line) {
            $data = preg_split('/\s+/', trim($line));

            if (count($data) == 7) {
                Attendance::create([
                    'employee_id' => $data[0],
                    'timestamp' => Carbon::createFromFormat('Y-m-d H:i:s', $data[1] . ' ' . $data[2]),
                    'status1' => (bool) $data[3],
                    'status2' => (bool) $data[4],
                    'status3' => (bool) $data[5],
                    'status4' => (bool) $data[6],
                    'status5' => (bool) $data[7], // Menggunakan data[6] karena data asli hanya memiliki 6 kolom
                ]);
            }
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

}
