{{-- Pterodactyl - Panel --}}
{{-- Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com> --}}

{{-- This software is licensed under the terms of the MIT license. --}}
{{-- https://opensource.org/licenses/MIT --}}
@extends('layouts.master')

@section('title')
    Staff System
@endsection

@section('content-header')
    <h1>Staff System<small>Request access to servers</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('index') }}">@lang('strings.home')</a></li>
        <li class="active">Staff System</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Server Accesses</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tbody>
                        <tr>
                            <th>Server</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                        @foreach ($requests as $request)
                            <tr>
                                <td>
                                    <a title="Click here to redirect" href="{{ route('server.index', $servers[array_search($request->server_id, array_column($servers, 'id'))]['uuidShort']) }}" target="_blank">
                                        {{$servers[array_search($request->server_id, array_column($servers, 'id'))]['name']}}
                                        -
                                        {{$servers[array_search($request->server_id, array_column($servers, 'id'))]['uuidShort']}}
                                    </a>
                                </td>
                                <td>{{$request->message}}</td>
                                <td>
                                    @switch($request->status)
                                        @case(1)
                                        <span class="label label-info">Waiting...</span>
                                        @break

                                        @case(2)
                                        <span class="label label-success">Accepted</span>
                                        @break

                                        @case(3)
                                        <span class="label label-danger">Denied</span>
                                        @break
                                    @endswitch
                                </td>
                                <td>{{$request->updated_at}}</td>
                                <td>
                                    <button data-action="delete" data-id="{{$request->id}}" class="btn btn-danger btn-xs" title="Delete Request"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-md-4">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Request Access</h3>
                </div>
                <form method="post" action="{{ route('staff.server.access.request') }}">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="server">Server</label>
                            <select id="server" name="server" class="form-control">
                                @foreach($servers as $server)
                                    <option value="{{$server['id']}}">{{$server['name']}} - {{$server['uuidShort']}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Why you want?</label>
                            <textarea id="message" name="message" class="form-control" placeholder="Why you want a server access?"></textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        <button type="submit" class="btn btn-success pull-right">Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {!! Theme::css('vendor/select2/select2.min.css?t={cache-version}') !!}
    @parent
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/select2/select2.full.min.js?t={cache-version}') !!}

    <script>
        $('#server').select2({
            placeholder: 'Select a Server',
        });

        $('[data-action="delete"]').click(function (event) {
            event.preventDefault();
            var self = $(this);
            swal({
                title: '',
                type: 'warning',
                text: 'Are you sure that you want to delete this request?',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#d9534f',
                closeOnConfirm: false,
                showLoaderOnConfirm: true,
            }, function () {
                $.ajax({
                    method: 'DELETE',
                    url: '{{ Route('staff.server.access.delete') }}',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
                    data: { id: self.data('id') }
                }).done(function () {
                    self.parent().parent().slideUp();
                    swal.close();
                }).fail(function (jqXHR) {
                    swal({
                        type: 'error',
                        title: 'Whoops!',
                        text: (typeof jqXHR.responseJSON.error !== 'undefined') ? jqXHR.responseJSON.error : 'An error occurred while processing this request.'
                    });
                });
            });
        });
    </script>
@endsection

