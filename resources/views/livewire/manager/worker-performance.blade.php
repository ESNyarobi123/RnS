<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Worker Performance') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Monitor {{ $workerTitle }} performance and productivity') }}</p>
    </div>

    {{-- Performance Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-terra/10">
                    <flux:icon.users class="size-5 text-terra" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Total {{ $workerTitle }}') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $performanceStats['total_workers'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100">
                    <flux:icon.presentation-chart-line class="size-5 text-emerald-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Avg Performance') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $performanceStats['average_performance'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-yellow-100">
                    <flux:icon.trophy class="size-5 text-yellow-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Top Performer') }}</p>
                    <p class="text-lg font-bold text-smoke">{{ $performanceStats['top_performer_name'] }}</p>
                    <p class="text-sm text-smoke-muted">Score: {{ $performanceStats['top_performer_score'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-blue-100">
                    <flux:icon.star class="size-5 text-blue-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Avg Rating') }}</p>
                    <p class="text-2xl font-bold text-smoke">4.5</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:select
                    wire:model.live="period"
                    class="w-full sm:w-40"
                >
                    <option value="today">{{ __('Today') }}</option>
                    <option value="week">{{ __('This Week') }}</option>
                    <option value="month">{{ __('This Month') }}</option>
                </flux:select>

                <flux:input
                    wire:model.live="search"
                    placeholder="{{ __('Search {{ $workerTitle }}...') }}"
                    class="w-full sm:w-64"
                />

                <flux:select
                    wire:model.live="sortBy"
                    class="w-full sm:w-48"
                >
                    <option value="performance_score">{{ __('Performance Score') }}</option>
                    <option value="tips_total">{{ __('Total Tips') }}</option>
                    <option value="orders_total">{{ __('Total Orders') }}</option>
                    <option value="rating_average">{{ __('Avg Rating') }}</option>
                </flux:select>

                <flux:button
                    wire:click="$set('sortDirection', $sortDirection === 'desc' ? 'asc' : 'desc')"
                    variant="ghost"
                    size="sm"
                >
                    <flux:icon.arrow-up-down class="size-4" />
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Top Performers --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <h3 class="mb-4 text-lg font-semibold text-smoke">{{ __('Top 5 Performers') }}</h3>
        <div class="space-y-3">
            @foreach ($topPerformers as $index => $worker)
                <div class="flex items-center justify-between p-3 rounded-lg bg-ivory-light/50">
                    <div class="flex items-center gap-3">
                        <div class="flex size-8 items-center justify-center rounded-full bg-terra/10 text-sm font-semibold text-terra">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="font-semibold text-smoke">{{ $worker->name }}</p>
                            <p class="text-sm text-smoke-muted">
                                {{ __('Score') }}: {{ round($worker->performance_score, 1) }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-smoke-muted">{{ __('Performance') }}</p>
                        <p class="font-semibold text-terra">{{ round($worker->performance_score, 1) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Workers Performance Table --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ivory-light">
                    <tr class="text-left text-sm text-smoke-muted">
                        <th class="px-6 py-3 font-medium">{{ __('{{ $workerTitle }}') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Performance Score') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Tips') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Orders') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Rating') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Customers Served') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ivory-dark/30">
                    @foreach ($workers as $worker)
                        <tr class="hover:bg-ivory-light transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 items-center justify-center rounded-full bg-terra/10">
                                        <flux:icon.user class="size-5 text-terra" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-smoke">{{ $worker->name }}</p>
                                        <p class="text-sm text-smoke-muted">{{ $worker->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-terra">{{ round($worker->performance_score, 1) }}</span>
                                    <div class="w-16 bg-ivory-dark/20 rounded-full h-2">
                                        <div 
                                            class="h-2 bg-terra rounded-full transition-all duration-300"
                                            style="width: {{ min(100, ($worker->performance_score / 100) * 100) }}%"
                                        ></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-smoke">${{ number_format($worker->tips_total, 2) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-smoke">{{ $worker->orders_total }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-smoke">{{ number_format($worker->rating_average, 1) }}</span>
                                    <div class="flex">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <flux:icon.star 
                                                class="size-4 {{ $i <= $worker->rating_average ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                {{ $i <= $worker->rating_average ? 'solid' : 'outline' }} 
                                            />
                                        @endfor
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-smoke">{{ $worker->customers_served }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <flux:button
                                        wire:click="$dispatch('view-worker-details', { workerId: {{ $worker->id }} })"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        <flux:icon.eye class="size-4" />
                                    </flux:button>
                                    
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm">
                                            <flux:icon.ellipsis-vertical class="size-4" />
                                        </flux:button>

                                        <flux:menu>
                                            <flux:menu.item icon="gift" wire:click="$dispatch('view-worker-tips', { workerId: {{ $worker->id }} })">
                                                {{ __('View Tips') }}
                                            </flux:menu.item>
                                            
                                            <flux:menu.item icon="star" wire:click="$dispatch('view-worker-feedback', { workerId: {{ $worker->id }} })">
                                                {{ __('View Feedback') }}
                                            </flux:menu.item>
                                            
                                            <flux:menu.item icon="clipboard-document-list" wire:click="$dispatch('view-worker-orders', { workerId: {{ $worker->id }} })">
                                                {{ __('View Orders') }}
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $workers->links() }}
    </div>

    {{-- Worker Details Modal --}}
    <flux:modal name="worker-details-modal" class="max-w-2xl">
        <flux:heading size="lg">
            {{ __('{{ $workerTitle }} Details') }}
        </flux:heading>

        <div id="worker-details-content">
            <!-- Content will be loaded dynamically -->
        </div>

        <div class="mt-4 flex justify-end">
            <flux:button
                wire:click="$dispatch('close-modal', 'worker-details-modal')"
                variant="primary"
            >
                {{ __('Close') }}
            </flux:button>
        </div>
    </flux:modal>

    @script
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('view-worker-details', (data) => {
                // Load worker details dynamically
                document.getElementById('worker-details-content').innerHTML = `
                    <div class="space-y-4">
                        <p>Loading worker details...</p>
                    </div>
                `;
                Livewire.dispatch('open-modal', { name: 'worker-details-modal' });
            });

            Livewire.on('view-worker-tips', (data) => {
                // Navigate to worker tips
                window.location.href = '/manager/tips?worker=' + data.workerId;
            });

            Livewire.on('view-worker-feedback', (data) => {
                // Navigate to worker feedback
                window.location.href = '/manager/feedbacks?worker=' + data.workerId;
            });

            Livewire.on('view-worker-orders', (data) => {
                // Navigate to worker orders
                window.location.href = '/manager/orders?worker=' + data.workerId;
            });
        });
    </script>
    @endscript
</div>
