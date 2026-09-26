<div class="space-y-8">


    <!-- Account Header Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-4 sm:gap-5">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-tr from-brand-500 to-pink-600 text-white font-extrabold text-2xl sm:text-3xl flex items-center justify-center shadow-md shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900">{{ $user->name }}</h1>
                    <span class="bg-brand-50 text-brand-700 text-[10px] font-bold uppercase px-2 py-0.5 rounded-md">
                        {{ ucfirst($user->role ?? 'Customer') }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 flex items-center gap-3 flex-wrap">
                    <span>📧 {{ $user->email }}</span>
                    <span>&bull;</span>
                    <span>📱 {{ $user->phone ?? 'No phone added' }}</span>
                </p>
                <p class="text-[11px] text-gray-400">Member since {{ $user->created_at->format('M Y') }}</p>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-2 gap-4 w-full md:w-auto">
            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-center min-w-[120px]">
                <span class="text-xs text-gray-400 font-medium block">Total Orders</span>
                <span class="text-lg font-extrabold text-gray-900">{{ $totalOrders }}</span>
            </div>
            <div class="p-4 bg-brand-50/50 rounded-2xl border border-brand-100 text-center min-w-[120px]">
                <span class="text-xs text-brand-600 font-medium block">Paid Spending</span>
                <span class="text-lg font-extrabold text-brand-700">BDT {{ number_format($totalSpent, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content Area with Navigation Tabs -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Tab Navigation (4 Cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-4 border border-gray-100 shadow-sm space-y-2">
            <button 
                type="button"
                wire:click="$set('activeTab', 'profile')"
                class="w-full text-left p-3.5 rounded-2xl text-xs font-bold transition flex items-center justify-between {{ $activeTab === 'profile' ? 'bg-brand-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                <span class="flex items-center gap-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Profile & Shipping Address</span>
                </span>
                <span>&rarr;</span>
            </button>

            <button 
                type="button"
                wire:click="$set('activeTab', 'orders')"
                class="w-full text-left p-3.5 rounded-2xl text-xs font-bold transition flex items-center justify-between {{ $activeTab === 'orders' ? 'bg-brand-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                <span class="flex items-center gap-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"/></svg>
                    <span>Order History & Tracking</span>
                </span>
                <span class="bg-white/20 text-xs px-2 py-0.5 rounded-full">{{ $totalOrders }}</span>
            </button>

            <button 
                type="button"
                wire:click="$set('activeTab', 'security')"
                class="w-full text-left p-3.5 rounded-2xl text-xs font-bold transition flex items-center justify-between {{ $activeTab === 'security' ? 'bg-brand-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                <span class="flex items-center gap-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Security & Password</span>
                </span>
                <span>&rarr;</span>
            </button>

            @if($user->isAdmin() || $user->isManager())
                <div class="pt-4 border-t border-gray-100 mt-4">
                    <a href="/admin" class="w-full text-left p-3.5 rounded-2xl text-xs font-bold bg-gray-900 text-white hover:bg-gray-800 transition flex items-center justify-between shadow-sm">
                        <span class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Open Admin Panel</span>
                        </span>
                        <span class="text-xs">&rarr;</span>
                    </a>
                </div>
            @endif

            <form action="{{ route('logout') }}" method="POST" class="pt-2">
                @csrf
                <button type="submit" class="w-full text-left p-3.5 rounded-2xl text-xs font-bold text-red-600 hover:bg-red-50 transition flex items-center gap-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>

        <!-- Right Tab Viewports (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm">
            
            <!-- Tab 1: Profile & Shipping Form -->
            @if($activeTab === 'profile')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Personal & Delivery Details</h3>
                        <p class="text-xs text-gray-500">Update your contact information for faster checkout & SMS dispatch.</p>
                    </div>

                    @if (session()->has('profile_success'))
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium">
                            ✓ {{ session('profile_success') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="updateProfile" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Full Name</label>
                                <input type="text" wire:model="name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                                @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Email Address</label>
                                <input type="email" wire:model="email" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                                @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Phone Number (For SMS & Tracking)</label>
                                <input type="tel" wire:model="phone" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                                @error('phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Default City / Region</label>
                                <select wire:model="city" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                                    <option value="inside_dhaka">Inside Dhaka (BDT 80)</option>
                                    <option value="outside_dhaka">Outside Dhaka (BDT 150)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Default Shipping Address</label>
                            <textarea wire:model="shipping_address" rows="3" placeholder="House/Road no, Flat, Area, Thana..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition"></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                                Save Profile Changes
                            </button>
                        </div>
                    </form>
                </div>

            <!-- Tab 2: Orders History -->
            @elseif($activeTab === 'orders')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Your Order History</h3>
                        <p class="text-xs text-gray-500">Track current shipments, view invoices, and review past purchases.</p>
                    </div>

                    @forelse($orders as $order)
                        <div class="p-5 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200/60 pb-3">
                                <div>
                                    <span class="text-xs font-bold font-mono text-gray-900">#{{ $order->order_number }}</span>
                                    <span class="text-[11px] text-gray-400 block">{{ $order->created_at->format('M d, Y · H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <!-- Order Status Badge -->
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase {{ match($order->order_status) {
                                        'delivered'  => 'bg-emerald-100 text-emerald-800',
                                        'shipped'    => 'bg-blue-100 text-blue-800',
                                        'processing' => 'bg-amber-100 text-amber-800',
                                        'cancelled'  => 'bg-red-100 text-red-800',
                                        default      => 'bg-gray-200 text-gray-800',
                                    } }}">
                                        {{ $order->order_status }}
                                    </span>

                                    <!-- Payment Status Badge -->
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $order->payment_status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Items Summary -->
                            <div class="divide-y divide-gray-100 text-xs space-y-1.5">
                                @foreach($order->orderItems as $item)
                                    <div class="flex justify-between py-1 text-gray-700">
                                        <span>{{ $item->product_name ?? 'Product' }} &times; {{ $item->quantity }}</span>
                                        <span class="font-semibold">BDT {{ number_format($item->total_price ?? ($item->price * $item->quantity), 2) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Bottom Row with Total and Actions -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-200/60 text-xs">
                                <div>
                                    <span class="text-gray-500">Total: </span>
                                    <span class="font-extrabold text-gray-900">BDT {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</span>
                                    <span class="text-[10px] text-gray-400 font-mono">({{ strtoupper($order->payment_method ?? 'COD') }})</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('order.track', $order->order_number) }}" wire:navigate class="px-3 py-1.5 bg-white border border-gray-300 hover:border-brand-500 text-gray-700 hover:text-brand-600 rounded-xl font-bold transition">
                                        Track Parcel &rarr;
                                    </a>
                                    <a href="{{ route('order.invoice.download', $order->order_number) }}" target="_blank" class="px-3 py-1.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl font-bold transition">
                                        Invoice PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 bg-gray-50 rounded-2xl space-y-3">
                            <span class="text-3xl">🛍️</span>
                            <h4 class="font-bold text-sm text-gray-900">No orders placed yet</h4>
                            <p class="text-xs text-gray-400">Discover our collection and make your first luxury purchase!</p>
                            <a href="{{ route('home') }}" wire:navigate class="inline-block px-5 py-2.5 bg-brand-600 text-white font-bold text-xs rounded-xl shadow">
                                Shop Now &rarr;
                            </a>
                        </div>
                    @endforelse
                </div>

            <!-- Tab 3: Security & Password -->
            @elseif($activeTab === 'security')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Account Security</h3>
                        <p class="text-xs text-gray-500">Update your login password regularly to protect your account.</p>
                    </div>

                    @if (session()->has('password_success'))
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium">
                            ✓ {{ session('password_success') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="updatePassword" class="space-y-4 max-w-md">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Current Password</label>
                            <input type="password" wire:model="current_password" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                            @error('current_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">New Password</label>
                            <input type="password" wire:model="new_password" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                            @error('new_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" wire:model="new_password_confirmation" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs rounded-xl shadow-md transition">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>
