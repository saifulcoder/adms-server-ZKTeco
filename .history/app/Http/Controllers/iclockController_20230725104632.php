<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class iclockController extends Controller
{
        /*  
    * Handle the incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function __invoke(Request $request)
   {
    
   }

    // handshake
    public function checkDevice(Request $request)
    {
        $data['url'] = \Request::getRequestUri();
        $data["data"] = json_encode($request->all());
        $data["sn"] = $request->SN;
        $data["option"] = $request>option;
        DB::table('device_log')->insert($data);

        // update status device
        DB::table('absensi_mesin')->where('no_sn', $request->SN)->update(['online' => now()]);
            $r = "GET OPTION FROM: ".$request->SN."
            Stamp=9999
            OpStamp=".strtotime('now')."
            ErrorDelay=60
            Delay=30
            ResLogDay=18250
            ResLogDelCount=10000
            ResLogCount=50000
            TransTimes=00:00;14:05
            TransInterval=1
            TransFlag=1111000000
            Realtime=1
            Encrypt=0";
            $r = trim(preg_replace('/\t+/', '', $r));
        // $r = "GET OPTION FROM:%s{$request->SN}\nStamp=1565089939\nOpStamp=1565089939\nErrorDelay=30\nDelay=10\nTransTimes=00:00;14:05\nTransInterval=1\nTransFlag=1111000000\nTimeZone=7\nRealtime=1\nEncrypt=0\n";
        
        return $r;
    }
    // implementasi https://docs.nufaza.com/docs/devices/zkteco_attendance/push_protocol/
    // setting timezone

    // request absensi
    public function receiveRecords(Request $request)
    {    
    // cek validasi device fingerprint berdasarkan serial number
    $cek = DB::table('absensi_mesin')->select('id')->where('no_sn','=',$request->SN)->first();
    if(is_null($cek)){return "ERROR";}

    try {
        $content = $request->getContent();
        $arr = preg_split('/\\r\\n|\\r|,|\\n/', $content);
        $jml = count($arr);

        foreach($arr as $rey){
            // $jam = $req[1];
            $req = preg_split('/\\t\\n|\\t|,|\\n/', $rey);
            if(!empty(trim($req[0])) ){
                    $jam = date('H:i:s', strtotime($req[1]));
                    // echo $jam;
                    $jadwal_sholat = DB::table("jadwal_sholat")
                    ->select("id")
                    ->where("dari","<=",$jam)
                    ->where("sampai",">=",$jam)
                    ->first();

                    $nis = trim($req[0]);
                    $data = [];
                    $data['table'] = $request->table;
                    $data['data'] = $content;
                    $data['id_jadwal_sholat'] = $jadwal_sholat->id; 
                    // $data['id_santri_mesin'] =  trim($req[0]);
                    $data['nis_santri'] =  $nis;
                    $data['waktu'] = $req[1];
                    $data['id_absensi_mesin'] = $cek->id;
                    if($jadwal_sholat->id){
                        DB::table('absensi_sholat')->insert($data);
                    }
                    // else{
                    //     DB::table('absensi_sholat_diluar_jam')->insert($data);
                    // }
            }
        }
            return "OK: ".$jml;
        } catch (Throwable $e) {
            $data['error'] = $e;
            
            DB::table('error_log')->insert($data);
            report($e);
    
            return "ERROR:".$jml."\n";
        }

        
        // if (isset($request->table)) {
        //     $table = $request->table;
        // } else {
        //     $this->doNothing();
        // }
        // switch ($table) {
        //     case 'ATTLOG':
        //         $this->savetoTable($request);
        //         $this->logAttendance($request);

        //         return $this->returnOk();
        //         break;
        //     case 'ATTPHOTO':
        //         //receiveOnSitePhoto($request);
        //         break;
        //     case 'OPERLOG':
        //         $this->savetoTable($request);
        //         $this->receiveOperationLog($request);
        //         break;
        //     default:
        //         $this->doNothing();
        //         break;
        // }
        // return $this->returnOk();
    }
   

}
