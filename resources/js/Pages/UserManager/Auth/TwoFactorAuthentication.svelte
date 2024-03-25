<script>
    import { onMount } from "svelte";
    import { router }  from "../../../util"
   
    let qrCode;
    let otp;
    
    onMount( async () => {

        await router.getWithToken("/api/2faa", { 
            onSuccess : (response) => {
               qrCode = response.qrCode;
            },
            onError : (error) => {
                console.log(error);
            }
        });

    })
   
   const verifyOTP = async () => {

        await router.postWithToken("/api/two_factor_verify", { otp }, {
            onSuccess : (response) => {
                console.log(response);
            },
            onError : (error) => {
                console.log(error);
            }
        });
   }

</script>

<h1>Hey There !!!</h1>
{ @html qrCode }

<input type="text" bind:value={ otp } class="border px-6 py-4">
<br>
<button class=" bg-black text-white px-6 py-2.5 rounded" on:click={ verifyOTP }>Verify</button>

