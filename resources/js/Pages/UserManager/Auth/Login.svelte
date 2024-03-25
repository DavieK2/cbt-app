<script>
    import { router } from "../../../util";
    import Button from "../../components/button.svelte";

    let email = "";
    let password = "";

    const login = async () => {

        disabled = true;

        await router.post("/adminer/login", { email, password }, {

            onSuccess : (response) => {
                
                localStorage.setItem("token", response.data.token);
                return router.navigateTo(response.data.url);
            },
            onError : (error) => {
                console.log(error);
            }
        });

        disabled = false;
    }

    $: disabled = ! ( email && password )
</script>

<div class="bg-white dark:bg-gray-900">
    <div class="flex justify-center h-screen">
        <div class="hidden bg-cover lg:block lg:w-2/3" style="background-image: url('/images/bg-image-cbt.avif')">
            <div class="flex items-center h-full px-20 bg-gray-900 bg-opacity-40">
                <div>
                    <div>
                        <h2 class="text-5xl font-bold text-white mt-10">SONCAL CBT</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center w-full max-w-md px-6 mx-auto lg:w-2/6">
            <div class="flex-1">
                <div class="text-center">
                    <div class="flex justify-center mx-auto">
                        <img class="w-auto h-20" src="/images/logo.png" alt="">
                    </div>

                    <p class="mt-3 text-gray-500 dark:text-gray-300">Sign in to access your account</p>
                </div>

                <div class="mt-8">
                        <div>
                            <label for="email" class="block mb-2 text-sm text-gray-600 dark:text-gray-200">Email Address</label>
                            <input bind:value={ email } type="email" name="email" id="email" placeholder="example@example.com" class="block w-full px-4 py-2 mt-2 text-gray-700 placeholder-gray-400 bg-white border border-gray-200 rounded-lg dark:placeholder-gray-600 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:border-blue-400 dark:focus:border-blue-400 focus:ring-blue-400 focus:outline-none focus:ring focus:ring-opacity-40" />
                        </div>

                        <div class="mt-6">
                            <div class="flex justify-between mb-2">
                                <label for="password" class="text-sm text-gray-600 dark:text-gray-200">Password</label>
                            </div>

                            <input bind:value={ password } type="password" name="password" id="password" placeholder="Your Password" class="block w-full px-4 py-2 mt-2 text-gray-700 placeholder-gray-400 bg-white border border-gray-200 rounded-lg dark:placeholder-gray-600 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:border-blue-400 dark:focus:border-blue-400 focus:ring-blue-400 focus:outline-none focus:ring focus:ring-opacity-40" />
                        </div>

                        <div class="mt-6">
                           <Button { disabled } on:click={ login } buttonText={ 'Sign In' } />
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>