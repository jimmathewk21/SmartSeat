<div class="action-buttons">
    @can($viewGate)
        <a class="btn btn-sm btn-outline-primary btn-icon action-view" href="{{ route('admin.' . $crudRoutePart . '.show', $row->id) }}" data-toggle="tooltip" title="{{ trans('global.view') }}" aria-label="{{ trans('global.view') }}">
            <i class="fas fa-eye" aria-hidden="true"></i>
        </a>
    @endcan
    @can($editGate)
        <a class="btn btn-sm btn-outline-secondary btn-icon action-edit" href="{{ route('admin.' . $crudRoutePart . '.edit', $row->id) }}" data-toggle="tooltip" title="{{ trans('global.edit') }}" aria-label="{{ trans('global.edit') }}">
            <i class="fas fa-pen" aria-hidden="true"></i>
        </a>
    @endcan
    @can($deleteGate)
        <form action="{{ route('admin.' . $crudRoutePart . '.destroy', $row->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon action-delete" data-toggle="tooltip" title="{{ trans('global.delete') }}" aria-label="{{ trans('global.delete') }}">
                <i class="fas fa-trash" aria-hidden="true"></i>
            </button>
        </form>
    @endcan
</div>
