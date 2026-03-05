@extends('admin.layouts.app')
@section('title', __('Disputes'))
@section('page-title', __('Disputes Management'))

@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('Order # or user...') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                    <option value="investigating" {{ request('status') == 'investigating' ? 'selected' : '' }}>{{ __('Investigating') }}</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Order') }}</th>
                        <th>{{ __('Filed By') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($disputes as $dispute)
                    <tr>
                        <td>{{ $dispute->id }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $dispute->order_id) }}" class="fw-semibold text-decoration-none">
                                {{ $dispute->order->order_number ?? '#' . $dispute->order_id }}
                            </a>
                        </td>
                        <td>{{ $dispute->raisedByUser->name ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $dispute->reason)) }}</span></td>
                        <td><small>{{ Str::limit($dispute->description, 60) }}</small></td>
                        <td>
                            @php $dsc = ['open' => 'danger', 'investigating' => 'warning', 'resolved' => 'success', 'closed' => 'secondary']; @endphp
                            <span class="badge bg-{{ $dsc[$dispute->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $dispute->status)) }}</span>
                        </td>
                        <td><small>{{ $dispute->created_at->format('d/m/Y') }}</small></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#disputeModal{{ $dispute->id }}">
                                <i class="fas fa-reply"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- Response Modal --}}
                    <div class="modal fade" id="disputeModal{{ $dispute->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('Respond to Dispute') }} #{{ $dispute->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('admin.disputes.resolve', $dispute->id) }}">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">{{ __('Original Complaint') }}</label>
                                            <p class="bg-light p-2 rounded">{{ $dispute->description }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">{{ __('Admin Response') }} *</label>
                                            <textarea name="resolution" rows="4" class="form-control" required>{{ $dispute->resolution }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">{{ __('Status') }}</label>
                                            <select name="status" class="form-select">
                                                <option value="investigating" {{ $dispute->status == 'investigating' ? 'selected' : '' }}>{{ __('Investigating') }}</option>
                                                <option value="resolved" {{ $dispute->status == 'resolved' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                                                <option value="closed" {{ $dispute->status == 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                        <button type="submit" class="btn btn-primary">{{ __('Send Response') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">{{ __('No disputes found.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($disputes->hasPages())
    <div class="card-footer bg-transparent">{{ $disputes->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
