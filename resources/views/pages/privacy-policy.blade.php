<x-layouts.app>
    <x-slot:title>Privacy Policy - {{ config('app.name', 'Kazi Fashion World') }}</x-slot>

    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 sm:p-12">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-6 font-serif">Privacy Policy</h1>
            <div class="prose prose-brand max-w-none text-gray-600 space-y-6">
                <p>Last updated: {{ date('F j, Y') }}</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">1. Information We Collect</h2>
                <p>We collect information you provide directly to us, such as when you create an account, make a purchase, or contact customer support. This includes your name, email address, phone number, shipping address, and payment information.</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">2. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Process your orders and manage your account.</li>
                    <li>Communicate with you about orders, products, services, and promotions.</li>
                    <li>Monitor and analyze trends, usage, and activities in connection with our website.</li>
                    <li>Personalize the website and provide advertisements, content, or features that match user profiles or interests.</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 mt-8">3. Tracking & Cookies</h2>
                <p>We use cookies and similar tracking technologies (such as Meta Pixel and Google Analytics) to track the activity on our Service and hold certain information. Cookies are files with small amount of data which may include an anonymous unique identifier. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">4. Data Security</h2>
                <p>The security of your data is important to us, but remember that no method of transmission over the Internet, or method of electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your Personal Data, we cannot guarantee its absolute security.</p>

                <h2 class="text-xl font-bold text-gray-900 mt-8">5. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us at privacy@example.com.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
