<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;

class StaffController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * StaffController constructor.
     * @param AlertsMessageBag $alert
     */
    public function __construct(AlertsMessageBag $alert)
    {
        $this->alert = $alert;
    }

    public function update(Request $request, $userId)
    {
        $this->validate($request, [
            'staff' => 'required|min:0|max:1'
        ]);

        DB::table('users')->where('id', '=', $userId)->update([
            'staff' => $request->input('staff')
        ]);

        $this->alert->success('ok')->flash();
        return redirect()->route('admin.users.view', 1);
    }
}
