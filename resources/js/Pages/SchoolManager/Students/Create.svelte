<script>
    import { onMount } from "svelte";
    import Webcam from "webcam-easy";
    import Button from "../../components/button.svelte";
    import Input from "../../components/input.svelte";
    import { router } from "../../../util";
    import Select from "../../components/select.svelte";


    let webcamElement;
    let canvasElement;
    let webcam;
    let profilePic;

    let firstName;
    let lastName;
    let regNo;
    let email;
    let session;
    let programOfStudy;
    let level;

    let classes = [];
    let sessions = []
    let programsOfStudy = [
        { placeholder: 'PRE-DEGREE SCIENCE', value: 'PRE-DEGREE SCIENCE'},
        { placeholder: 'PRE-DEGREE AGRIC', value: 'PRE-DEGREE AGRIC'},
        { placeholder: 'PRE-DEGREE ARTS', value: 'PRE-DEGREE ARTS'},
        { placeholder: 'PRE-DEGREE EDUCATION', value: 'PRE-DEGREE EDUCATION'},
        { placeholder: 'PRE-DEGREE SOCIAL STUDIES', value: 'PRE-DEGREE SOCIAL STUDIES'},
        { placeholder: 'PRE-DEGREE MANAGEMENT SCIENCE', value: 'PRE-DEGREE MANAGEMENT SCIENCE'},
        { placeholder: 'PRE-DEGREE FRENCH', value: 'PRE-DEGREE FRENCH'}
    ]
   

    onMount(async () => {

        webcam = new Webcam(webcamElement, 'user', canvasElement);

         router.get('/api/classes', {

            onSuccess : (res) => {
                classes = res.data.flatMap((grade) => [{ placeholder : grade.class_name, value : grade.id }])
            }
        })

        router.get('/api/sessions', {

            onSuccess : (res) => {
                sessions = res.data.flatMap((session) => [{ placeholder : session.session, value : session.id }])
            }
        })
    })

    const start = () => {

        webcam.start().then( result => {
            console.log('webcam started')
        }).catch( err => {
            console.log(err);
        })
    }

    const capture = () => {

        profilePic = webcam.snap();

    }

     const stop = () =>  webcam.stop();

     const saveStudentInfo = () => {

        router.post('/api/student-profile/create', { firstName: firstName.value, lastName: lastName.value, regNo: regNo.value, profilePic, level: level.value, session: session.value, programOfStudy: programOfStudy.value, email: email.value }, {
            onSuccess : (res) => {
                window.location.reload();
            }
        })
     }

</script>

<div class="flex min-h-screen w-screen">
    <div class="container min-h-screen flex flex-1 flex-col">
        <div class="flex flex-col py-12">
            <h1 class="font-bold text-lg">Student Bio Data Capture</h1>
            <div class="space-y-6 mt-12">
                <Input bind:this={ firstName } label="Enter Student First Name"/>
                <Input bind:this={ lastName } label="Enter Student Surname"/>
                <Input bind:this={ email } label="Enter Student Email"/>
                <Input bind:this={ regNo } label="Enter Form Number"/>
                <Select bind:this={ programOfStudy }  options={ programsOfStudy } placeholder="Select Program of Study" label="Select Program of Study" className="text-sm"  />
                <Select bind:this={ level }  options={ classes } placeholder="Select Level" label="Select Student Level" className="text-sm"  />
                <Select bind:this={ session }  options={ sessions } placeholder="Select Session" label="Select Session" className="text-sm"  />
                <div>
                    <p class="text-sm">Student Passport</p>
                    <canvas class="h-60 w-60 mt-6 border-2 rounded-lg" bind:this={ canvasElement } id="canvas"></canvas>
                </div>
            </div>

           <div class="mt-10">
                <Button buttonText="Save Student Info" on:click={ saveStudentInfo } />
           </div>
        </div>
    </div>
    <div class="container min-h-screen flex flex-col items-center pt-16 w-1/2 border-l border-2">
        <div>
            <!-- svelte-ignore a11y-media-has-caption -->
            <video class="border rounded-lg" bind:this={ webcamElement } id="webcam" autoplay playsinline width="640" height="480"></video>
        </div>

        <div class="flex space-x-5 mt-12">
            <Button buttonText="Start Webcam" on:click={ start } className="max-w-fit" />
            <Button buttonText="Capture" on:click={ capture } className="max-w-fit"  />
            <Button buttonText="Stop Webcam" on:click={ stop } className="max-w-fit"  />
        </div>
        
    </div>
</div>
