<?php

namespace Pterodactyl\Http\Controllers\Base;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;

class RequestAccessToServerController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * RequestAccessToServerController constructor.
     * @param AlertsMessageBag $alert
     */
    public function __construct(AlertsMessageBag $alert)
    {
        $this->alert = $alert;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $requests = DB::table('staff_requests')->get();
        foreach ($requests as $request) {
            if ($request->status == 2) {
                $issetSubuser = DB::table('subusers')->where('server_id', '=', $request->server_id)->where('user_id', '=', $request->staff_id)->get();
                if (count($issetSubuser) < 1) {
                    DB::table('staff_requests')->where('id', '=', $request->id)->update([
                        'status' => 3
                    ]);
                }
            }
        }

        $requests = DB::table('staff_requests')->get();
        $servers = DB::table('servers')->get();

        return view('staff.request', [
            'requests' => $requests,
            'servers' => json_decode(json_encode($servers), true)
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function request(Request $request)
    {
        $this->validate($request, [
            'server' => 'required|int',
            'message' => 'required'
        ]);

        if (!Server::find($request->input('server'))) {
            $this->alert->danger('Server not found.')->flash();
        } else {
            $issetRequest = DB::table('staff_requests')->where('staff_id', '=', Auth::user()->id)->where('server_id', '=', $request->input('server'))->get();
            if (count($issetRequest) > 0) {
                $this->alert->danger('You have an access to this server.')->flash();
            } else {
                DB::table('staff_requests')->insert([
                    'staff_id' => Auth::user()->id,
                    'server_id' => $request->input('server'),
                    'message' => trim(strip_tags($request->input('message'))),
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                $this->alert->success('You have successfully submitted your request.')->flash();
            }
        }

        return redirect()->route('staff.server.access');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $id = (int) $request->input('id');

        $requests = DB::table('staff_requests')->where('staff_id', '=', Auth::user()->id)->where('id', '=', $id)->get();
        if (count($requests) < 1) {
            return response()->json(['error' => 'Request not found.'])->setStatusCode(500);
        }

        DB::table('staff_requests')->where('staff_id', '=', Auth::user()->id)->where('id', '=', $id)->delete();

        $subuser = DB::table('subusers')->where('server_id', '=', $requests[0]->server_id)->where('user_id', '=', Auth::user()->id)->get();
        DB::table('subusers')->where('server_id', '=', $requests[0]->server_id)->where('user_id', '=', Auth::user()->id)->delete();
        if (count($subuser) > 0) {
            DB::table('permissions')->where('subuser_id', '=', $subuser[0]->id)->delete();
        }

        return response()->json(['success' => true]);
    }
}
