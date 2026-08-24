@extends('layouts.admin.app')

@section('title', 'Partner API Keys')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title"><i class="tio-key-outlined"></i> Partner API Keys</h1>
            </div>
        </div>
    </div>

    @if(session('new_key_secret'))
        <div class="card mb-3" style="border:1.5px solid var(--brand,#F37021)">
            <div class="card-body">
                <h5 class="mb-2">New key created — copy the secret now, it will not be shown again</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label class="input-label">Key ID (X-Gizra-Key)</label>
                        <input type="text" class="form-control" readonly value="{{ session('new_key_id') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="input-label">Secret (used for HMAC signing, never sent in plaintext again)</label>
                        <input type="text" class="form-control" readonly value="{{ session('new_key_secret') }}">
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row gx-2 gx-lg-3">
        <div class="col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title">Issue new key</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.partner-api-keys.store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="input-label">Restaurant</label>
                            <select name="restaurant_id" class="form-control" required>
                                <option value="" disabled selected>Select restaurant</option>
                                @foreach($restaurants as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="input-label">Key name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. POS integration" required>
                        </div>
                        <div class="form-group">
                            <label class="input-label">Scopes</label>
                            @foreach($availableScopes as $scope)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="scopes[]" value="{{ $scope }}" id="scope-{{ $scope }}">
                                    <label class="form-check-label" for="scope-{{ $scope }}">{{ $scope }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-group">
                            <label class="input-label">IP allowlist (optional, comma-separated)</label>
                            <input type="text" name="ip_allowlist" class="form-control" placeholder="203.0.113.4, 203.0.113.5">
                        </div>
                        <button type="submit" class="btn btn--primary">Create key</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title">Active keys</h5></div>
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Restaurant</th>
                                <th>Name</th>
                                <th>Key ID</th>
                                <th>Scopes</th>
                                <th>Last used</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($keys as $key)
                                <tr>
                                    <td>{{ $key->restaurant->name ?? '—' }}</td>
                                    <td>{{ $key->name }}</td>
                                    <td><code>{{ $key->key_id }}</code></td>
                                    <td>{{ implode(', ', $key->scopes ?? []) }}</td>
                                    <td>{{ $key->last_used_at?->diffForHumans() ?? 'never' }}</td>
                                    <td>
                                        @if($key->isRevoked())
                                            <span class="badge badge-soft-danger">Revoked</span>
                                        @else
                                            <span class="badge badge-soft-success">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$key->isRevoked())
                                            <form action="{{ route('admin.partner-api-keys.revoke', $key->id) }}" method="post" onsubmit="return confirm('Revoke this key? Integrations using it will stop working immediately.')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn--danger">Revoke</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 pb-3">{!! $keys->links() !!}</div>
            </div>
        </div>
    </div>

    <div class="row gx-2 gx-lg-3 mt-2">
        <div class="col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title">Register webhook</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.partner-api-keys.webhooks.store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="input-label">Restaurant</label>
                            <select name="restaurant_id" class="form-control" required>
                                <option value="" disabled selected>Select restaurant</option>
                                @foreach($restaurants as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="input-label">Endpoint URL (HTTPS only)</label>
                            <input type="url" name="url" class="form-control" placeholder="https://partner.example.com/webhooks/gizra" required>
                        </div>
                        <div class="form-group">
                            <label class="input-label">Events</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="events[]" value="order.created" id="ev-created">
                                <label class="form-check-label" for="ev-created">order.created</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="events[]" value="order.status_changed" id="ev-status">
                                <label class="form-check-label" for="ev-status">order.status_changed</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary">Register webhook</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title">Registered webhooks</h5></div>
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Restaurant</th>
                                <th>URL</th>
                                <th>Events</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($webhooks as $wh)
                                <tr>
                                    <td>{{ $wh->restaurant->name ?? '—' }}</td>
                                    <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $wh->url }}</td>
                                    <td>{{ implode(', ', $wh->events ?? []) }}</td>
                                    <td>
                                        @if($wh->active)
                                            <span class="badge badge-soft-success">Active</span>
                                        @else
                                            <span class="badge badge-soft-danger">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.partner-api-keys.webhooks.toggle', $wh->id) }}" method="post">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn--secondary">{{ $wh->active ? 'Disable' : 'Enable' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 pb-3">{!! $webhooks->links() !!}</div>
            </div>
        </div>
    </div>
</div>
@endsection
