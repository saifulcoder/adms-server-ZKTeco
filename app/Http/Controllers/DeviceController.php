<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\Datatables;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Attendance;
use DB;

class DeviceController extends Controller
{
    // Menampilkan daftar device
    public function index(Request $request)
    {
        $data['title'] = "Biometric Devices";
        $data['log'] = Device::all();
        return view('devices.index',$data);
    }

    public function DeviceLog(Request $request)
    {
        $data['lable'] = "Devices Log";
        $data['log'] = DB::table('device_log')->select('id','data','url')->orderBy('id','DESC')->get();
        
        return view('devices.log',$data);
    }
    
    public function FingerLog(Request $request)
    {
        $data['lable'] = "Finger Log";
        $data['log'] = DB::table('finger_log')->select('id','data','url')->orderBy('id','DESC')->get();
        return view('devices.log',$data);
    }
    public function Attendance() {
       //$attendances = Attendance::latest('timestamp')->orderBy('id','DESC')->paginate(15);
       $attendances = DB::table('attendances')->select('id','sn','table','stamp','employee_id','timestamp','status1','status2','status3','status4','status5')->orderBy('id','DESC')->paginate(15);

        return view('devices.attendance', compact('attendances'));
        
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(Request $request)
    {
        $device = new Device();
        $device->name = $request->input('name');
        $device->serial_number = $request->input('no_sn');
        $device->idreloj = $request->input('idreloj');
        $device->save();

         return redirect()->route('devices.index')->with('success', 'Biometrico actualizado correctamente');
    }

    public function show($id)
    {
         $device = Device::find($id);
         return view('devices.show', compact('device'));
    }

    public function edit($id)
    {
        $device = Device::find($id);
        return view('devices.edit', compact('device'));
    }

    public function update(Request $request, $id)
    {
        $device = Device::find($id);
        $device->name = $request->input('name');
        $device->no_sn = $request->input('no_sn');
        $device->idreloj = $request->input('idreloj');
        $device->save();
      return redirect()->route('devices.index')->with('success', 'Biométrico actualizado correctamente');
    }

    // // Menghapus device dari database
    // public function destroy($id)
    // {
    //     $device = Device::find($id);
    //     $device->delete();

    //     return redirect()->route('devices.index')->with('success', 'Device berhasil dihapus!');
    // }
}
