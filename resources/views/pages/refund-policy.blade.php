<x-layouts.app>
    <x-slot:title>Return & Refund Policy - {{ config('app.name', 'Kazi Fashion World') }}</x-slot>

    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 sm:p-12">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-6 font-serif">Return & Refund Policy</h1>
            <div class="prose prose-brand max-w-none text-gray-600 space-y-6">
                <p>Last updated: {{ date('F j, Y') }}</p>

                <p>Thank you for shopping at {{ config('app.name') }}. If, for any reason, You are not completely satisfied with a purchase We invite You to review our policy on refunds and returns.</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">1. Conditions for Returns</h2>
                <p>In order for the Goods to be eligible for a return, please make sure that:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>The Goods were purchased in the last 7 days.</li>
                    <li>The Goods are in the original packaging.</li>
                    <li>The Goods were not used or damaged.</li>
                    <li>You have the receipt or proof of purchase.</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 mt-8">2. Non-returnable Items</h2>
                <p>The following Goods cannot be returned:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Goods made to Your specifications or clearly personalized.</li>
                    <li>Goods which according to their nature are not suitable to be returned, deteriorate rapidly or where the date of expiry is over.</li>
                    <li>Goods which are not suitable for return due to health protection or hygiene reasons and were unsealed after delivery (e.g. Cosmetics, Skincare).</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 mt-8">3. Refund Process</h2>
                <p>We will reimburse You no later than 14 days from the day on which We receive the returned Goods. We will use the same means of payment as You used for the Order, and You will not incur any fees for such reimbursement.</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">4. Returning Goods</h2>
                <p>You are responsible for the cost and risk of returning the Goods to Us. You should send the Goods to the address provided by our support team upon initiating a return request.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
