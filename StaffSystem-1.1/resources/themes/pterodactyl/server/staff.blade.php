{{-- Pterodactyl - Panel --}}
{{-- Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com> --}}

{{-- This software is licensed under the terms of the MIT license. --}}
{{-- https://opensource.org/licenses/MIT --}}
@extends('layouts.master')

@section('title')
    Staff System
@endsection

@section('content-header')
    <h1>Staff System</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('index') }}">@lang('strings.home')</a></li>
        <li><a href="{{ route('server.index', $server->uuidShort) }}">{{ $server->name }}</a></li>
        <li>@lang('navigation.server.configuration')</li>
        <li class="active">Staff System</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title mt-3">Access Requests</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tbody>
                        <tr>
                            <th>Staff</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                        @foreach ($requests as $request)
                            <tr>
                                <td>
                                    {{$users[array_search($request->staff_id, array_column((array) $users, 'id'))]->username}}
                                    -
                                    {{$users[array_search($request->staff_id, array_column((array) $users, 'id'))]->email}}
                                </td>
                                <td>{{$request->message}}</td>
                                <td id="status-{{$request->id}}">
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
                                <td id="date-{{$request->id}}">{{$request->updated_at}}</td>
                                <td id="actions-{{$request->id}}">
                                    @if ($request->status == 1)
                                        <button id="action-accept-{{$request->id}}" data-action="accept" data-id="{{$request->id}}" title="Accept"
                                                class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                                    @endif
                                    @if ($request->status != 3)
                                    <button id="action-deny-{{$request->id}}" data-action="deny" data-id="{{$request->id}}" title="Deny"
                                            class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                                    @endif
                                    @if ($request->status == 3)
                                        <span class="label label-warning">Nothing</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Help</h3>
                </div>
                <div class="box-body">
                    Staff can send you a request to manage this server. You can <span
                        style="color: green;">accept</span> and <span style="color: red;">deny</span> access request to
                    your server.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('[data-action="accept"]').click(function (event) {
            event.preventDefault();
            var self = $(this);
            swal({
                title: 'Accept',
                type: 'warning',
                text: 'Are you sure that you want to accept this request?',
                showCancelButton: true,
                confirmButtonText: 'Accept',
                confirmButtonColor: '#32a852',
                closeOnConfirm: false,
                showLoaderOnConfirm: true,
            }, function () {
                $.ajax({
                    method: 'POST',
                    url: '{{ Route('server.staff.accept', $server->uuidShort) }}',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
                    data: { id: self.data('id') }
                }).done(function (response) {
                    $('#status-' + self.data('id')).html('<span class="label label-success">Accepted</span>');
                    $('#date-' + self.data('id')).text(response.date.date.split('.')[0]);
                    $('#action-accept-' + self.data('id')).hide(500);
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

        $('[data-action="deny"]').click(function (event) {
            event.preventDefault();
            var self = $(this);
            swal({
                title: 'Deny',
                type: 'warning',
                text: 'Are you sure that you want to deny this request?',
                showCancelButton: true,
                confirmButtonText: 'Deny',
                confirmButtonColor: '#d9534f',
                closeOnConfirm: false,
                showLoaderOnConfirm: true,
            }, function () {
                $.ajax({
                    method: 'POST',
                    url: '{{ Route('server.staff.deny', $server->uuidShort) }}',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
                    data: { id: self.data('id') }
                }).done(function (response) {
                    $('#status-' + self.data('id')).html('<span class="label label-danger">Denied</span>');
                    $('#date-' + self.data('id')).text(response.date.date.split('.')[0]);
                    $('#actions-' + self.data('id')).html('<span class="label label-warning">Nothing</span>');
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
