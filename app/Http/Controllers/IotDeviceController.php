<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IotDevice;
use App\Models\ActivityLog;

class IotDeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = IotDevice::query()->orderBy('created_at', 'desc');
        
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'like', "%{$s}%")
                  ->orWhere('location_name', 'like', "%{$s}%")
                  ->orWhere('uid', 'like', "%{$s}%");
        }

        $devices = $query->paginate(15)->withQueryString();
        return view('iot_devices.index', compact('devices'));
    }

    public function create()
    {
        return view('iot_devices.form', ['device' => new IotDevice()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'uid'              => 'nullable|string|max:100|unique:iot_devices,uid',
            'location_name'    => 'nullable|string|max:255',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'ip_address'       => 'nullable|string|max:45',
            'firmware_version' => 'nullable|string|max:50',
            'status'           => 'required|in:active,inactive,maintenance',
            'installed_at'     => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        $device = IotDevice::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action'  => 'Create Device',
            'details' => "Menambahkan perangkat baru: {$device->name}"
        ]);

        return redirect()->route('iot-devices.index')->with('success', 'Perangkat berhasil ditambahkan!');
    }

    public function edit(IotDevice $iotDevice)
    {
        return view('iot_devices.form', ['device' => $iotDevice]);
    }

    public function update(Request $request, IotDevice $iotDevice)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'uid'              => 'nullable|string|max:100|unique:iot_devices,uid,' . $iotDevice->id,
            'location_name'    => 'nullable|string|max:255',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'ip_address'       => 'nullable|string|max:45',
            'firmware_version' => 'nullable|string|max:50',
            'status'           => 'required|in:active,inactive,maintenance',
            'installed_at'     => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        // Catat perubahan jika status direvisi manjadi inactive, dll
        $iotDevice->update($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action'  => 'Update Device',
            'details' => "Memperbarui info perangkat: {$iotDevice->name}"
        ]);

        return redirect()->route('iot-devices.index')->with('success', 'Info perangkat berhasil diperbarui!');
    }

    public function destroy(IotDevice $iotDevice)
    {
        $name = $iotDevice->name;
        $iotDevice->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action'  => 'Delete Device',
            'details' => "Menghapus perangkat secara permanen: {$name}"
        ]);

        return redirect()->route('iot-devices.index')->with('success', 'Perangkat berhasil dihapus!');
    }
}
