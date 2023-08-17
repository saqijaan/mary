<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Table extends Component
{
    public string $uuid;

    public function __construct(
        public array $headers,
        public Collection $rows,
        public ?bool $zebra = false,

        // Slots
        public mixed $actions = null,
        public mixed $tr = null,
        public mixed $cell = null
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div class="overflow-x-auto">
                    <table 
                        {{ $attributes->class([
                                'table', 
                                'table-zebra' => $zebra,
                                'cursor-pointer' => $attributes->has('@row-click')]) 
                        }} 
                    >
                        <!-- HEADERS -->
                        <thead class="text-black">
                            <tr>
                                @foreach($headers as $index => $header)
                                    <th @class(["w-1" => $index == 0])>
                                        {{ $header['label'] }}
                                    </th>
                                @endforeach

                                <!-- ACTIONS (Just a empty column) -->
                                @if($actions)
                                    <th class="w-1"></th>
                                @endif 
                            </tr>
                        </thead>
                        
                        <!-- ROWS -->
                        <tbody>                                                                                
                            @foreach($rows as $row)                                                  
                                <tr class="hover:bg-base-200/50" @click="$dispatch('row-click', {{ $row }});">                                                        
                                @foreach($headers as $header)    
                                    @php 
                                        # Scoped slot`s name like `user.city` are compiled to `user___city` through `@scope / @endscope`.
                                        # So we user current `$header` key  to find that slot on context.
                                        $temp_key = str_replace('.', '___', $header['key']) 
                                    @endphp
                                    
                                    @if(isset(${"cell_".$temp_key}))
                                        <td>
                                            {{ ${"cell_".$temp_key}($row)  }}
                                        </td>                                                                    
                                    @else
                                        <td>{{ data_get($row, $header['key']) }}</td>                                
                                    @endif
                                @endforeach
                                
                                <!-- ACTIONS -->
                                @if($actions)                                        
                                    <td class="text-right" @click="event.stopPropagation()">{{ $actions($row) }}</td>
                                @endif                         
                                </tr>                       
                            @endforeach
                        </tbody>
                    </table>
                </div>
            HTML;
    }
}
