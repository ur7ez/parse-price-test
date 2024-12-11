<!-- ====== Subscribe Section Start -->
<section class="bg-white dark:bg-primary py-4 overflow-hidden relative z-10">
    <div class="container">
        <div class="">
            <div class="my-3 text-center">
                <h1 class="text-lg font-bold text-gray-900">Subscribe to OLX Price change alerts</h1>
            </div>
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="container pt-1 mt-3 mx-auto max-w-lg bg-white p-6 rounded-lg shadow-lg">
                <form id="subscriptionForm" method="POST" class=""
                      x-data="
                                {
                                    formData: {
                                        email: '',
                                        urls: '',
                                    },
                                    errors: {},
                                    successMessage: '',
                                    submitForm(event) {
                                        this.successMessage = '';
                                        this.errors = {};
                                        fetch('{{ route('subscribe') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'X-CSRF-TOKEN': document.querySelector(`meta[name='csrf-token']`).getAttribute('content')
                                            },
                                            body: JSON.stringify(this.formData),
                                        })
                                        .then(response => {
                                            if (response.status === 200) {
                                                return response.json();
                                            }
                                            throw response;
                                        })
                                        .then(result => {
                                            this.formData = {
                                              email: '',
                                              urls: '',
                                            };
                                            this.successMessage = result.message;
                                        })
                                        .catch(async (response) => {
                                            const res = await response;
                                            if (response.status === 422) {
                                              this.errors = res.errors;
                                            }
                                        })
                                    }
                                }
                                "
                      x-on:submit.prevent="submitForm"
                >
                    <template x-if="successMessage">
                        <div class="py-4 px-6 bg-green-600 text-gray-100 mb-4 rounded-md relative">
                            <!-- Notification Text -->
                            <span x-html="successMessage"></span>
                            <!-- Close Button -->
                            <button
                                @click="successMessage = ''"
                                class="absolute top-2 right-2 text-gray-200 hover:text-white">
                                &times;
                            </button>
                        </div>
                    </template>
                    @csrf
                    <div class="space-y-6">
                        <div class="sm:col-span-4">
                            <label for="email" class="block text-sm font-medium text-gray-900">Email address:</label>
                            <div class="mt-2">
                                <x-forms.input type="email" placeholder="email for price notification" name="email" value="{{ old('email') }}" required x-model="formData.email" ::class="errors.email ? 'border-red-500 focus:border-red-500' : ''"></x-forms.input>
                            </div>
                            <template x-if="errors.email">
                                <div x-text="errors.email[0]" class="text-red-500 rounded-md"></div>
                            </template>
                        </div>
                        <div class="sm:col-span-4">
                            <label for="urls" class="block text-sm font-medium text-gray-900">Adver URL(s) to monitor for price
                                changes:</label>
                            <div class="mt-2">
                                <x-forms.textarea placeholder="Enter one URL per line" name="urls" rows="5" required x-model="formData.urls" ::class="errors.urls ? 'border-red-500 focus:border-red-500' : ''">{ old('urls') ? implode("\n", old('urls')) : '' }}</x-forms.textarea>
                                <template x-if="errors.urls">
                                    <div x-text="errors.urls[0]" class="text-red-500 rounded-md"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 text-right">
                        <button type="submit"
                                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Subscribe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<!-- ====== Subscribe Section End -->