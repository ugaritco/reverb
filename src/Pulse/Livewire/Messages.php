<?php

namespace Ugarit\Reverb\Pulse\Livewire;

use Heritage\Contracts\Support\Htmlable;
use Heritage\Support\Facades\Config;
use Heritage\Support\Facades\Request;
use Heritage\Support\Facades\View;
use Heritage\Support\HtmlString;
use Ugarit\Pulse\Livewire\Card;
use Ugarit\Pulse\Livewire\Concerns\HasPeriod;
use Ugarit\Pulse\Livewire\Concerns\RemembersQueries;
use Ugarit\Reverb\Pulse\Recorders\ReverbMessages;
use Livewire\Attributes\Lazy;

class Messages extends Card
{
    use Concerns\HasRate,
        HasPeriod,
        RemembersQueries;

    /**
     * The graph colors.
     */
    public array $colors = [
        'received' => '#10b981',
        'received:per_rate' => '#78d7b3',
        'sent' => '#9333ea',
        'sent:per_rate' => '#bc81f1',
    ];

    /**
     * Render the component.
     */
    #[Lazy]
    public function render()
    {
        [$all, $time, $runAt] = $this->remember(fn () => [
            $readings = $this->graph(['reverb_message:sent', 'reverb_message:received'], 'count'),
            $readings->map->map(fn ($values) => $values->map($this->rate(...))),
        ]);

        [$messages, $messagesRate] = $all;

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('reverb-messages-chart-update', messages: $messages, messagesRate: $messagesRate);
        }

        return View::make('reverb::livewire.messages', [
            'messages' => $messages,
            'messagesRate' => $messagesRate,
            'time' => $time,
            'runAt' => $runAt,
            'config' => Config::get('pulse.recorders.'.ReverbMessages::class),
        ]);
    }

    /**
     * Define any CSS that should be loaded for the component.
     *
     * @return string|Htmlable|array<int, string|Htmlable>|null
     */
    protected function css(): HtmlString
    {
        return new HtmlString(
            '<style>'.
            collect($this->colors)->map(fn ($color) => '.bg-\\[\\'.$color.'\\]{background-color:'.$color.'}')->join('').
            '</style>'
        );
    }
}
