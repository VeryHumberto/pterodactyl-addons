<?php

namespace Pterodactyl\Http\Controllers\Server;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\Permission;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Traits\Controllers\JavascriptInjection;

class StaffController extends Controller
{
    use JavascriptInjection;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    private $alert;

    /**
     * StaffController constructor.
     * @param AlertsMessageBag $alert
     */
    public function __construct(AlertsMessageBag $alert)
    {
        $this->alert = $alert;
    }

    /**
     * @param Request $request
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): View
    {
        $server = $request->attributes->get('server');
        $this->authorize('view-staff', $server);
        $this->setRequest($request)->injectJavascript();

        $requests = DB::table('staff_requests')->where('server_id', '=', $server->id)->get();
        foreach ($requests as $request) {
            if ($request->status == 2) {
                $issetSubuser = DB::table('subusers')->where('server_id', '=', $server->id)->where('user_id', '=', $request->staff_id)->get();
                if (count($issetSubuser) < 1) {
                    DB::table('staff_requests')->where('id', '=', $request->id)->update([
                        'status' => 3
                    ]);
                }
            }
        }

        $users = DB::table('users')->where('staff', '=', 1)->get();
        $requests = DB::table('staff_requests')->where('server_id', '=', $server->id)->get();

        return view('server.staff', [
            'users' => $users,
            'requests' => $requests
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accept(Request $request)
    {
        $id = (int) $request->input('id');
        $server = $request->attributes->get('server');

        try {
            $this->authorize('view-staff', $server);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'You don\'t have an access to this page.'])->setStatusCode(500);
        }

        $accessRequest = DB::table('staff_requests')->where('id', '=', $id)->where('server_id', '=', $server->id)->get();
        if (count($accessRequest) < 1) {
            return response()->json(['error' => 'Request not found.'])->setStatusCode(500);
        }

        if ($accessRequest[0]->status == 2) {
            return response()->json(['error' => 'You have already accepted this request.'])->setStatusCode(500);
        }

        DB::table('staff_requests')->where('id', '=', $id)->where('server_id', '=', $server->id)->update([
            'status' => 2,
            'updated_at' => Carbon::now()
        ]);

        $id = DB::table('subusers')->insertGetId([
            'user_id' => $accessRequest[0]->staff_id,
            'server_id' => $server->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $permissions = Permission::getPermissions();
        foreach ($permissions as $daemon => $permission) {
            foreach ($permission as $key => $item) {
                DB::table('permissions')->insert([
                    'subuser_id' => $id,
                    'permission' => $key
                ]);
            }
        }

        return response()->json(['success' => true, 'date' => Carbon::now()]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deny(Request $request)
    {
        $id = (int) $request->input('id');
        $server = $request->attributes->get('server');

        try {
            $this->authorize('view-staff', $server);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'You don\'t have an access to this page.'])->setStatusCode(500);
        }

        $accessRequest = DB::table('staff_requests')->where('id', '=', $id)->where('server_id', '=', $server->id)->get();
        if (count($accessRequest) < 1) {
            return response()->json(['error' => 'Request not found.'])->setStatusCode(500);
        }

        if ($accessRequest[0]->status == 3) {
            return response()->json(['error' => 'You have already denied this request.'])->setStatusCode(500);
        }

        DB::table('staff_requests')->where('id', '=', $id)->where('server_id', '=', $server->id)->update([
            'status' => 3,
            'updated_at' => Carbon::now()
        ]);

        $subuser = DB::table('subusers')->where('server_id', '=', $server->id)->where('user_id', '=', $accessRequest[0]->staff_id)->get();
        DB::table('subusers')->where('server_id', '=', $server->id)->where('user_id', '=', $accessRequest[0]->staff_id)->delete();
        if (count($subuser) > 0) {
            DB::table('permissions')->where('subuser_id', '=', $subuser[0]->id)->delete();
        }

        return response()->json(['success' => true, 'date' => Carbon::now()]);
    }
}
