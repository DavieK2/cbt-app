<script>
    import Icons from "../../../../components/icons.svelte";
    import Layout from "../../../../layouts/Layout.svelte";
    import Button from "../../../../components/button.svelte";
    import { router } from "../../../../../../js/util";
    import { page } from "@inertiajs/svelte";
    import Input from "../../../../components/input.svelte";

    let assessmentTitle = $page.props.assessmentTitle;
    let assessmentId = $page.props.assessmentId;

   
    let disabled = false

    let title = '';


    const createQuestionBank = () => {

        disabled = true;


        router.postWithToken('/api/question-bank-create', { assessmentId, title }, {

            onSuccess : (res) => {

                router.navigateTo(`/assessments/question_bank/sections/${res.data.questionBankId}`);
            },

            onError : (res) => {
                disabled = false;
            }
        } )
    }

    $: disabled = title.length === 0

</script>

<Layout>
  
    <div class="mt-20 flex items-center justify-between border-b bg-white">
        <div class="flex space-x-3 items-center py-4 px-8">
            <Icons icon="chart" className="h-5 w-5" />
            <span class="mx-2 text-sm font-medium text-gray-700">Question Bank</span>
            <Icons icon="chevron_right" className="h-4 w-4 fill-gray-800" />
            <span class="text-sm font-medium text-gray-700">{ assessmentTitle }</span>
        </div>
    </div>

    <div class="w-full mx-auto h-full"> 
        <div class="container max-w-4xl my-12 space-x-10">
            <div class="border bg-white p-8 rounded-lg max-w-8xl">
                <div class="flex items-center justify-between my-2 border-b pb-8">
                    <div>
                        <div class="text-lg font-semibold min-w-max text-gray-700">Create Question Bank</div>
                        <div class="text-gray-400 text-sm">Please select relevant details below</div>
                    </div>
                </div>
        
                <div class="mt-12 space-y-6 w-full">
                  
                    <Input on:input={ (e) => title = e.detail.input } placeholder="Enter Question Bank Title" label="Enter Question Bank Title" labelStyle="font-bold" className="w-full" />
                   
                </div>

                <div class="flex w-full mt-10">
                    <div class="w-40">
                        <Button on:click={ createQuestionBank } { disabled } buttonText="Continue" />
                    </div>
                </div>
            </div>
        </div>
    </div>

</Layout>