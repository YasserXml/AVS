{{-- resources/views/folders/folderaction.blade.php --}}
<x-filament-actions::action
    :action="$action"
    dynamic-component="filament::button"
    :label="$getLabel()"
    :size="$getSize()"
    class="fi-ac-icon-btn-action w-full h-full"
    color="gray"
>
    <style>
        .folder-icon-{{$item->id}} {
            width: 80px;
            height: 55px;
            background-color: {{$item->color ?? '#f3c623'}};
            border-radius: 4px;
            position: relative;
            margin-top: 15px;
            margin-right: 8px;
            margin-left: 8px;
            margin-bottom: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .folder-icon-{{$item->id}}::before {
            content: "";
            width: 30px;
            height: 8px;
            background-color: {{$item->color ?? '#f3c623'}};
            border-radius: 4px 4px 0 0;
            position: absolute;
            top: -8px;
            left: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
    <div class="flex flex-col justify-center items-center gap-3 p-3 rounded-lg hover:shadow-md transition-shadow">
        <div class="folder-icon-{{$item->id}} flex flex-col items-center justify-center">
            @if($item->icon)
                <x-icon name="{{$item->icon}}" class="text-white w-5 h-5"/>
            @endif
        </div>
        <div class="flex flex-col items-center justify-center my-1">
            <div>
                <h1 class="font-semibold text-base text-center">{{ $item->name }}</h1>
            </div>
            @if($item->description)
                <div class="mt-1">
                    <p class="text-xs text-gray-600 dark:text-gray-400 text-center">{{ $item->description }}</p>
                </div>
            @endif
            <div class="flex justify-center mt-1">
                <p class="text-gray-600 dark:text-gray-300 text-xs">
                    {{ $item->created_at->diffForHumans() }}
                </p>
            </div>
            @if($item->is_protected)
                <div class="flex justify-center mt-1">
                    <x-icon name="heroicon-o-lock-closed" class="w-3 h-3 text-yellow-500"/>
                </div>
            @endif
        </div>
    </div>
</x-filament-actions::action>