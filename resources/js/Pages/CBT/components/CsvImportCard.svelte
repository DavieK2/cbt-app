<svelte:options accessors />

<script>
    import { createEventDispatcher } from "svelte";

    import Button from "../../components/button.svelte";
    
    export let uploadFile = undefined
    export let disabled = false;

    const dispatch = createEventDispatcher();

    let filePlaceholder = "Choose import file";
    let files;
    
    $: if (files) {
        for (const file of files) {
            filePlaceholder = `${ file.name }: ${ (file.size / (1000 * 1000)).toFixed(2) } MB`;   
        }
        uploadFile = files[0];
    }

    $: disabled = ! ( files )

</script>


<div class="flex flex-col px-2.5 space-y-4">
    <div class="">
        <h1 class="font-bold text-lg text-gray-800">Upload File</h1>
        <p class="text-gray-500 text-xs mx-auto mt-3">
            Click here to download the template 
        </p>
    </div>
    <div class="bg-red-50 border border-orange-300 p-5 rounded-lg">
        <h4 class="font-bold text-xs">
            <i class="fa-solid fa-triangle-exclamation text-yellow-400" /> Import only a CSV or Excel Files
        </h4>
        <small class="text-gray-500">Supported file type are ( .csv | .xlsx | .xls )</small>
    </div>

    <div class={`border-dashed border-2 grid gap-5 py-5 rounded-lg ${ true ? 'active' : ''}`}>

        <input
            type="file"
            bind:files
            accept=".csv, .xlsx"
            required
            id="importBtn"
            class="hidden"
        />
        <label for="importBtn" class="cursor-pointer flex flex-col items-center space-y-5">
            <img src="/images/file-icon.png" alt="" class="w-1/6 mx-auto"/>
            <span class="border-2 mx-auto p-2.5 cursor-pointer rounded-md text-xs bg-gray-300">{ filePlaceholder }</span>
        </label>
       
    </div>

    <div class="w-40 pt-8">
        <Button { disabled } on:click={ () => dispatch('on-upload') } buttonText="Upload" />
    </div>
</div>