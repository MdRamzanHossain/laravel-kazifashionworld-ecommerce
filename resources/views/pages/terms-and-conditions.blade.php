<x-layouts.app>
    <x-slot:title>Terms & Conditions - {{ config('app.name', 'Kazi Fashion World') }}</x-slot>

    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 sm:p-12">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-6 font-serif">Terms & Conditions</h1>
            <div class="prose prose-brand max-w-none text-gray-600 space-y-6">
                <p>Last updated: {{ date('F j, Y') }}</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">1. Acceptance of Terms</h2>
                <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement. In addition, when using this website's particular services, you shall be subject to any posted guidelines or rules applicable to such services.</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">2. Products and Pricing</h2>
                <p>All products listed on the website are subject to availability. We reserve the right to modify prices without prior notice. The pricing of products applies to all orders at the time of checkout. Any promotions, discounts, or flash sales may be subject to additional conditions.</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">3. Payments & Orders</h2>
                <p>By placing an order, you confirm that the payment details provided are valid and correct. We reserve the right to cancel or refuse any order at our discretion, particularly in cases of suspected fraud or pricing errors.</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">4. Intellectual Property</h2>
                <p>The Site and its original content, features, and functionality are owned by {{ config('app.name') }} and are protected by international copyright, trademark, patent, trade secret, and other intellectual property or proprietary rights laws.</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">5. Governing Law</h2>
                <p>These Terms shall be governed and construed in accordance with the laws of Bangladesh, without regard to its conflict of law provisions.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
