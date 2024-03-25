<script>
    import { router } from "../../../util";
    import { createEventDispatcher, onMount } from "svelte";
    import StarterKit from "@tiptap/starter-kit";
    import Table from '@tiptap/extension-table'
    import TableCell from '@tiptap/extension-table-cell'
    import TableHeader from '@tiptap/extension-table-header'
    import TableRow from '@tiptap/extension-table-row'
    import { Editor, Node, mergeAttributes } from "@tiptap/core";
    import Image from '@tiptap/extension-image'
    import Icons from "../../components/icons.svelte";
    import katex from 'katex';
    import Input from "../../components/input.svelte";
    import Button from "../../components/button.svelte";
    import { fade } from "svelte/transition";

    export let isOptionEditor = false;
    export let showTools = false
    export let content = '<p></p>';
    export let id;
    
    let element;
    let images = [];

    let formula = '';

    let showTableTools = false;
    let isTableActive = false;

    let row;
    let column;

    const dispatch = createEventDispatcher();

    let editor;
    
    let canClear = false;

    let questionTypes = {
        objectives: 'objectives',
        picture: 'picture',
        video: 'video',
        math: 'math',
        theory: 'theory',
    }

    let selectedQuestionType = questionTypes.objectives;

    const Math = Node.create({
        
        name: 'Math',
        
        defining: true,

        content: 'inline*',

        group: 'inline',

        inline: true,

        addAttributes() {
            return {
                display: {
                    default: 'hidden',
                },
                formula: {
                    default: null
                },
                cursorPos : {
                    default: 1
                },
                contenteditable:{
                    default:false
                }

            }
        },
        // addOptions() {
        //     return {
        //         HTMLAttributes: {},
        //         includeChildren: true,
        //     }
        // },
        parseHTML() {
            return [
                {
                    tag: 'katex',
                }
            ]
        },
        renderHTML({ HTMLAttributes }) {
            return ['katex', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)]
        },
        
        addNodeView() {
            return ({ node, editor, getPos }) => {

                const { view } = editor;

                const fraction = '<svg class="svg-icon" style="width: 1em; height: 1em;vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M398.44977778 428.94222222h-31.51644445l-2.50311111-69.40444444h-1.82044444c-14.79111111 27.42044445-32.31288889 46.99022222-52.45155556 58.82311111-20.13866667 11.83288889-43.12177778 17.74933333-68.83555555 17.74933333-36.75022222 0-65.42222222-9.10222222-86.24355556-27.19288889-20.70755555-18.09066667-31.17511111-43.23555555-31.17511111-75.43466666 0-37.43288889 13.99466667-65.42222222 41.87022222-84.08177778 27.87555555-18.65955555 71.90755555-28.78577778 131.98222223-30.37866667l66.21866666-2.16177777v-22.86933334c0-36.75022222-7.96444445-63.60177778-24.00711111-80.66844444-15.92888889-17.06666667-38.00177778-25.6-66.21866667-25.6-30.03733333 0-52.56533333 4.77866667-67.47022222 14.44977778-14.90488889 9.67111111-25.25866667 25.94133333-30.94755556 48.81066666l-31.85777777-6.82666666c8.87466667-30.94755555 23.66577778-53.02044445 44.48711111-65.99111112 20.82133333-12.97066667 49.60711111-19.456 86.016-19.456 41.75644445 0 72.81777778 10.92266667 93.41155556 32.768 20.48 21.84533333 30.72 55.86488889 30.72 102.17244445V428.94222222z m-149.16266667-21.73155555c19.56977778 0 37.888-5.34755555 55.06844444-15.92888889s31.40266667-25.6 42.78044445-45.056 16.95288889-38.79822222 16.95288889-58.14044445v-44.71466666l-62.91911111 2.16177778c-51.31377778 1.70666667-87.72266667 9.44355555-109.34044445 23.43822222s-32.42666667 35.49866667-32.42666666 64.62577778c0 22.64177778 7.168 40.61866667 21.61777778 53.81688888 14.56355555 13.08444445 37.31911111 19.79733333 68.26666666 19.79733334zM947.08622222 803.49866667c0 58.48177778-13.19822222 103.53777778-39.70844444 135.28177778S843.88977778 986.45333333 796.21688889 986.45333333c-27.98933333 0-51.76888889-5.80266667-71.33866667-17.52177778-19.56977778-11.71911111-35.84-30.15111111-48.69688889-55.296h-1.36533333c0 6.144-0.45511111 17.408-1.36533333 33.56444445-0.91022222 16.15644445-1.59288889 26.96533333-2.048 32.54044445h-30.60622222c1.59288889-29.80977778 2.38933333-53.93066667 2.38933333-72.47644445V480.02844445h32.99555555v171.57688888c0 8.07822222-0.11377778 15.81511111-0.34133333 23.552-0.22755555 7.73688889-0.56888889 15.47377778-1.024 23.552h1.36533333c14.90488889-27.30666667 32.08533333-46.64888889 51.54133334-58.14044444 19.456-11.49155555 44.60088889-17.18044445 75.32088888-17.18044444 45.85244445 0 81.35111111 15.81511111 106.38222223 47.44533333 25.14488889 31.63022222 37.66044445 75.88977778 37.66044444 132.66488889z m-33.33688889 0c0-49.94844445-9.78488889-87.83644445-29.35466666-113.664-19.56977778-25.82755555-48.128-38.68444445-85.44711112-38.68444445-39.13955555 0-69.40444445 12.62933333-90.79466666 37.77422223-21.27644445 25.25866667-31.97155555 64.28444445-31.97155556 117.1911111 0 49.94844445 9.89866667 87.83644445 29.58222222 113.77777778 19.68355555 25.94133333 49.03822222 38.79822222 87.95022223 38.79822222 39.59466667 0 69.51822222-13.42577778 89.77066667-40.16355555 20.25244445-26.624 30.26488889-64.96711111 30.26488888-115.02933333zM204.00355555 781.76711111l673.67822223-713.27288889 32.19911111 30.37866667-673.67822222 713.38666666-32.19911112-30.49244444z"  /></svg>';
                const sqrt = `<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
                              <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 -890.8 1425 1060" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" style=""><defs><path id="MJX-25-TEX-N-32" d="M109 429Q82 429 66 447T50 491Q50 562 103 614T235 666Q326 666 387 610T449 465Q449 422 429 383T381 315T301 241Q265 210 201 149L142 93L218 92Q375 92 385 97Q392 99 409 186V189H449V186Q448 183 436 95T421 3V0H50V19V31Q50 38 56 46T86 81Q115 113 136 137Q145 147 170 174T204 211T233 244T261 278T284 308T305 340T320 369T333 401T340 431T343 464Q343 527 309 573T212 619Q179 619 154 602T119 569T109 550Q109 549 114 549Q132 549 151 535T170 489Q170 464 154 447T109 429Z"></path><path id="MJX-25-TEX-N-221A" d="M95 178Q89 178 81 186T72 200T103 230T169 280T207 309Q209 311 212 311H213Q219 311 227 294T281 177Q300 134 312 108L397 -77Q398 -77 501 136T707 565T814 786Q820 800 834 800Q841 800 846 794T853 782V776L620 293L385 -193Q381 -200 366 -200Q357 -200 354 -197Q352 -195 256 15L160 225L144 214Q129 202 113 190T95 178Z"></path><path id="MJX-25-TEX-I-1D465" d="M52 289Q59 331 106 386T222 442Q257 442 286 424T329 379Q371 442 430 442Q467 442 494 420T522 361Q522 332 508 314T481 292T458 288Q439 288 427 299T415 328Q415 374 465 391Q454 404 425 404Q412 404 406 402Q368 386 350 336Q290 115 290 78Q290 50 306 38T341 26Q378 26 414 59T463 140Q466 150 469 151T485 153H489Q504 153 504 145Q504 144 502 134Q486 77 440 33T333 -11Q263 -11 227 52Q186 -10 133 -10H127Q78 -10 57 16T35 71Q35 103 54 123T99 143Q142 143 142 101Q142 81 130 66T107 46T94 41L91 40Q91 39 97 36T113 29T132 26Q168 26 194 71Q203 87 217 139T245 247T261 313Q266 340 266 352Q266 380 251 392T217 404Q177 404 142 372T93 290Q91 281 88 280T72 278H58Q52 284 52 289Z"></path></defs><g stroke="currentColor" fill="currentColor" stroke-width="0" transform="scale(1,-1)"><g data-mml-node="math"><g data-mml-node="mroot"><g><g data-mml-node="mi" transform="translate(853,0)"><use data-c="1D465" xlink:href="#MJX-25-TEX-I-1D465"></use></g></g><g data-mml-node="mn" transform="translate(261.8,380.8) scale(0.5)"><use data-c="32" xlink:href="#MJX-25-TEX-N-32"></use></g><g data-mml-node="mo" transform="translate(0,30.8)"><use data-c="221A" xlink:href="#MJX-25-TEX-N-221A"></use></g><rect width="572" height="60" x="853" y="770.8"></rect></g></g></g></svg>
                             `

                const log = `<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
                             <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 -666 1487.5 823.8" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" style=""><defs><path id="MJX-16-TEX-N-31" d="M213 578L200 573Q186 568 160 563T102 556H83V602H102Q149 604 189 617T245 641T273 663Q275 666 285 666Q294 666 302 660V361L303 61Q310 54 315 52T339 48T401 46H427V0H416Q395 3 257 3Q121 3 100 0H88V46H114Q136 46 152 46T177 47T193 50T201 52T207 57T213 61V578Z"></path><path id="MJX-16-TEX-N-30" d="M96 585Q152 666 249 666Q297 666 345 640T423 548Q460 465 460 320Q460 165 417 83Q397 41 362 16T301 -15T250 -22Q224 -22 198 -16T137 16T82 83Q39 165 39 320Q39 494 96 585ZM321 597Q291 629 250 629Q208 629 178 597Q153 571 145 525T137 333Q137 175 145 125T181 46Q209 16 250 16Q290 16 318 46Q347 76 354 130T362 333Q362 478 354 524T321 597Z"></path><path id="MJX-16-TEX-I-1D465" d="M52 289Q59 331 106 386T222 442Q257 442 286 424T329 379Q371 442 430 442Q467 442 494 420T522 361Q522 332 508 314T481 292T458 288Q439 288 427 299T415 328Q415 374 465 391Q454 404 425 404Q412 404 406 402Q368 386 350 336Q290 115 290 78Q290 50 306 38T341 26Q378 26 414 59T463 140Q466 150 469 151T485 153H489Q504 153 504 145Q504 144 502 134Q486 77 440 33T333 -11Q263 -11 227 52Q186 -10 133 -10H127Q78 -10 57 16T35 71Q35 103 54 123T99 143Q142 143 142 101Q142 81 130 66T107 46T94 41L91 40Q91 39 97 36T113 29T132 26Q168 26 194 71Q203 87 217 139T245 247T261 313Q266 340 266 352Q266 380 251 392T217 404Q177 404 142 372T93 290Q91 281 88 280T72 278H58Q52 284 52 289Z"></path></defs><g stroke="currentColor" fill="currentColor" stroke-width="0" transform="scale(1,-1)"><g data-mml-node="math"><g data-mml-node="msub"><g data-mml-node="mn"><use data-c="31" xlink:href="#MJX-16-TEX-N-31"></use><use data-c="30" xlink:href="#MJX-16-TEX-N-30" transform="translate(500,0)"></use></g><g data-mml-node="mi" transform="translate(1033,-150) scale(0.707)"><use data-c="1D465" xlink:href="#MJX-16-TEX-I-1D465"></use></g></g></g></g></svg>
                            `

                const sqr = `<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
                             <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 -725.5 1487.5 747.5" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" style=""><defs><path id="MJX-28-TEX-N-31" d="M213 578L200 573Q186 568 160 563T102 556H83V602H102Q149 604 189 617T245 641T273 663Q275 666 285 666Q294 666 302 660V361L303 61Q310 54 315 52T339 48T401 46H427V0H416Q395 3 257 3Q121 3 100 0H88V46H114Q136 46 152 46T177 47T193 50T201 52T207 57T213 61V578Z"></path><path id="MJX-28-TEX-N-30" d="M96 585Q152 666 249 666Q297 666 345 640T423 548Q460 465 460 320Q460 165 417 83Q397 41 362 16T301 -15T250 -22Q224 -22 198 -16T137 16T82 83Q39 165 39 320Q39 494 96 585ZM321 597Q291 629 250 629Q208 629 178 597Q153 571 145 525T137 333Q137 175 145 125T181 46Q209 16 250 16Q290 16 318 46Q347 76 354 130T362 333Q362 478 354 524T321 597Z"></path><path id="MJX-28-TEX-I-1D465" d="M52 289Q59 331 106 386T222 442Q257 442 286 424T329 379Q371 442 430 442Q467 442 494 420T522 361Q522 332 508 314T481 292T458 288Q439 288 427 299T415 328Q415 374 465 391Q454 404 425 404Q412 404 406 402Q368 386 350 336Q290 115 290 78Q290 50 306 38T341 26Q378 26 414 59T463 140Q466 150 469 151T485 153H489Q504 153 504 145Q504 144 502 134Q486 77 440 33T333 -11Q263 -11 227 52Q186 -10 133 -10H127Q78 -10 57 16T35 71Q35 103 54 123T99 143Q142 143 142 101Q142 81 130 66T107 46T94 41L91 40Q91 39 97 36T113 29T132 26Q168 26 194 71Q203 87 217 139T245 247T261 313Q266 340 266 352Q266 380 251 392T217 404Q177 404 142 372T93 290Q91 281 88 280T72 278H58Q52 284 52 289Z"></path></defs><g stroke="currentColor" fill="currentColor" stroke-width="0" transform="scale(1,-1)"><g data-mml-node="math"><g data-mml-node="msup"><g data-mml-node="mn"><use data-c="31" xlink:href="#MJX-28-TEX-N-31"></use><use data-c="30" xlink:href="#MJX-28-TEX-N-30" transform="translate(500,0)"></use></g><g data-mml-node="mi" transform="translate(1033,413) scale(0.707)"><use data-c="1D465" xlink:href="#MJX-28-TEX-I-1D465"></use></g></g></g></g></svg>
                            `
                const pi = `<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="0.8em" height="0.8em" viewBox="0 -431 570 442" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" style=""><defs><path id="MJX-31-TEX-I-1D70B" d="M132 -11Q98 -11 98 22V33L111 61Q186 219 220 334L228 358H196Q158 358 142 355T103 336Q92 329 81 318T62 297T53 285Q51 284 38 284Q19 284 19 294Q19 300 38 329T93 391T164 429Q171 431 389 431Q549 431 553 430Q573 423 573 402Q573 371 541 360Q535 358 472 358H408L405 341Q393 269 393 222Q393 170 402 129T421 65T431 37Q431 20 417 5T381 -10Q370 -10 363 -7T347 17T331 77Q330 86 330 121Q330 170 339 226T357 318T367 358H269L268 354Q268 351 249 275T206 114T175 17Q164 -11 132 -11Z"></path></defs><g stroke="currentColor" fill="currentColor" stroke-width="0" transform="scale(1,-1)"><g data-mml-node="math"><g data-mml-node="mi"><use data-c="1D70B" xlink:href="#MJX-31-TEX-I-1D70B"></use></g></g></g></svg>
                            `
                const theta = `<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
                               <svg xmlns="http://www.w3.org/2000/svg" width="0.8em" height="0.8em"  viewBox="0 -705 469 715" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" style=""><defs><path id="MJX-5-TEX-I-1D703" d="M35 200Q35 302 74 415T180 610T319 704Q320 704 327 704T339 705Q393 701 423 656Q462 596 462 495Q462 380 417 261T302 66T168 -10H161Q125 -10 99 10T60 63T41 130T35 200ZM383 566Q383 668 330 668Q294 668 260 623T204 521T170 421T157 371Q206 370 254 370L351 371Q352 372 359 404T375 484T383 566ZM113 132Q113 26 166 26Q181 26 198 36T239 74T287 161T335 307L340 324H145Q145 321 136 286T120 208T113 132Z"></path></defs><g stroke="currentColor" fill="currentColor" stroke-width="0" transform="scale(1,-1)"><g data-mml-node="math"><g data-mml-node="mi"><use data-c="1D703" xlink:href="#MJX-5-TEX-I-1D703"></use></g></g></g></svg>
                              `
                const plusminus = `<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
                                   <svg xmlns="http://www.w3.org/2000/svg" width="0.8em" height="0.8em"  viewBox="0 -666 778 666" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" style=""><defs><path id="MJX-6-TEX-N-B1" d="M56 320T56 333T70 353H369V502Q369 651 371 655Q376 666 388 666Q402 666 405 654T409 596V500V353H707Q722 345 722 333Q722 320 707 313H409V40H707Q722 32 722 20T707 0H70Q56 7 56 20T70 40H369V313H70Q56 320 56 333Z"></path></defs><g stroke="currentColor" fill="currentColor" stroke-width="0" transform="scale(1,-1)"><g data-mml-node="math"><g data-mml-node="mo"><use data-c="B1" xlink:href="#MJX-6-TEX-N-B1"></use></g></g></g></svg>
                                  `
                const element = document.createElement('div');
                const math = document.createElement('button');
                const wrapper = document.createElement('div');
                const grid = document.createElement('div');
                const button1 = document.createElement('button');
                const button2 = document.createElement('button');
                const button3 = document.createElement('button');
                const button4 = document.createElement('button');
                const button5 = document.createElement('button');
                const button6 = document.createElement('button');
                const button7 = document.createElement('button');
                const domContent = document.createElement('textarea');
                const updateButton = document.createElement('button');

                
                domContent.contentEditable = 'true';
                math.contentEditable = 'true';
                domContent.value = node.attrs.formula == 'a + b + c' ? '' : node.attrs.formula

                updateButton.textContent = 'Update'

                katex.render(node.attrs.formula, math, {
                    output: "mathml",
                    throwOnError: false,
                    maxSize: 300
                });

                wrapper.append(grid, domContent, updateButton);
                grid.append(button1, button2, button3, button4, button5, button6, button7)

                grid.setAttribute('class', 'grid grid-cols-7 h-8 border rounded mb-2');

                button1.setAttribute('class', 'border-r flex items-center justify-center');
                button2.setAttribute('class', 'border-r flex items-center justify-center');
                button3.setAttribute('class', 'border-r flex items-center justify-center');
                button4.setAttribute('class', 'border-r flex items-center justify-center');
                button5.setAttribute('class', 'border-r flex items-center justify-center');
                button6.setAttribute('class', 'border-r flex items-center justify-center');
                button7.setAttribute('class', 'border-r flex items-center justify-center');
                updateButton.setAttribute('class', 'p-2.5 rounded bg-gray-800 text-white text-xs mt-1')

                button1.innerHTML = fraction;
                button2.innerHTML = sqrt;
                button3.innerHTML = log;
                button4.innerHTML = sqr;
                button5.innerHTML = pi;
                button6.innerHTML = theta;
                button7.innerHTML = plusminus;

                button1.addEventListener('click', () => insertFraction(domContent));
                button2.addEventListener('click', () => insertSquareRoot(domContent));
                button3.addEventListener('click', () => insertSubscript(domContent));
                button4.addEventListener('click', () => insertSuperscript(domContent));
                button5.addEventListener('click', () => insertPi(domContent));
                button6.addEventListener('click', () => insertTheta(domContent));
                button7.addEventListener('click', () => insertPlusMinus(domContent));

                domContent.setAttribute('class', 'p-2.5 resize-none w-full h-10 border rounded')
                element.setAttribute('class', 'relative mx-1 inline-block');
                wrapper.setAttribute('class', `border ${node.attrs.display} rounded-lg bg-white  absolute -mt-40 w-[18rem] p-4`)
                
                element.append(wrapper, math);

                updateButton.addEventListener('click', (e) => {

                    if (typeof getPos === 'function') {
                       
                        view.dispatch(view.state.tr.setNodeMarkup(getPos(), undefined, {
                            ...node.attrs,
                            formula: domContent.value,
                        }));

                        editor.commands.focus();

                    }
                });

                math.addEventListener('click', (evt) => {

                    if (typeof getPos === 'function') {

                        if(  node.attrs.display === 'hidden' ){
                            
                            editor.setEditable(false);

                        }else{

                            editor.setEditable(true);
                        }

                        view.dispatch(view.state.tr.setNodeAttribute(getPos(), 'display', node.attrs.display === 'hidden' ? 'block' : 'hidden'));

                        editor.commands.focus()
                    }
                });
               

                return {
                    dom: element,
                    update:(e) => {
                        return false
                    },
                    contentDOM: math
                }
            }
        },
        
    })


    const textEditor = (node) => {        

        editor = new Editor({
            autofocus: true,
            element: node,
            extensions: [
                StarterKit,
                Math,
                Image.configure({
                    allowBase64: true,
                    HTMLAttributes: {
                        class: `rounded-lg ${ isOptionEditor ? 'h-20' : ''} w-auto border-2 mb-4`,
                    },
                }),
                Table.configure({
                    resizable: true,
                }),
                TableRow.configure({
                    HTMLAttributes: {
                        class: `rounded-lg border-2`,
                    },
                }),
                TableHeader.configure({
                    HTMLAttributes: {
                        class: `rounded-lg border-2 p-2`,
                    },
                }),
                TableCell.configure({
                    HTMLAttributes: {
                        class: `rounded-lg border-2 p-2`,
                    },
                }),
            ],
            editorProps: {
                attributes: {
                    class: `text-sm prose prose-sm sm:prose lg:prose-lg xl:prose-2xl mx-auto focus:outline-none ${ isOptionEditor ? 'min-h-11 w-full' : 'min-h-[16rem]'} ${ showTools ? 'py-6' : '' }`,
                },
            },
            onUpdate({ editor }) {

                formatInput(editor);
                 
            },
            content: content,
            onTransaction: () => {
               
                editor = editor
                formatInput(editor);
  
            },
        })

    };

    const formatInput = (editor) => {

        let editorContents = editor.getJSON()['content'];
        editorContents = editorContents[0]['content'];

        let content;

        if( editor.isEmpty ){
        
            return dispatch('input', { content:'', type: "none" });
        }

        if( isOptionEditor && editor.getJSON()['content']?.some( (content) => content.type === "image" ) ){

            content = editor.getJSON()['content']?.filter((content) => content.type === "image")[0]?.attrs;

            return dispatch('input', { content: content.src, type: "image", alt: content.alt });

        } 

        if( isOptionEditor && editorContents?.some( (content) => content.type === "Math" ) ){

            content = editorContents.filter((content) => content.type === "Math")[0]?.attrs.formula;

            return dispatch('input', { content, type: "Math" });

        } 

        if( isOptionEditor && editor.getText() && ( ! editorContents?.some( (content) => content.type === "Math") )){

            content = editor.getText();

            return dispatch('input', { content, type: "text" });
            
        } 

        if( ! isOptionEditor ){

            content = editor.getJSON();

            return dispatch('input', { content, type: "html" });

        }
    }

    const setSelectedQuestionType = (questionType) => selectedQuestionType = questionType; 

    const addImage = () => {

        let imageInput = document.getElementById(`pic${id}`);
        let reader = new FileReader();

        if( ! imageInput?.files[0] ) return;
        
        reader.readAsDataURL(imageInput?.files[0]);

        reader.onload = () => {

            if( isOptionEditor ){

                editor.commands.clearContent(true);
                editor.setEditable(false);
                editor.chain().focus().setImage({ src: reader.result, alt: 'ques_image' }).run();
                canClear = true

            }else{

                editor.chain().focus().setImage({ src: reader.result, alt: 'ques_image' }).run();
            }

            imageInput.value = ''

        }
    }

    const clickImageFile = () => {

        return document.getElementById(`pic${id}`).click();
    }

    const clearContent = () => {

        editor.commands.clearContent(true);
        editor.setEditable(true);
        canClear = false

        return dispatch('input', { content: '', type: "none" });
    }

    const insertFormula = () => {
        editor.commands.insertContent('<katex formula="a+b+c">a + b + c</katex>');
    }


    const insertFraction = (node) => {

        let startPosition = node.selectionStart;
        let endPosition = node.selectionEnd;

        node.setRangeText('\\cfrac{a}{b}', startPosition, endPosition, 'select');

        node.focus()
    }

    const insertSquareRoot = (node) => {

        let startPosition = node.selectionStart;
        let endPosition = node.selectionEnd;

        node.setRangeText('\\sqrt[2]{b}', startPosition, endPosition, 'select');

        node.focus()
    }

    const insertSubscript = (node) => {

        let startPosition = node.selectionStart;
        let endPosition = node.selectionEnd;

        node.setRangeText('10_x', startPosition, endPosition, 'select');

        node.focus()
    }

    const insertSuperscript = (node) => {

        let startPosition = node.selectionStart;
        let endPosition = node.selectionEnd;

        node.setRangeText('10^x', startPosition, endPosition, 'select');

        node.focus()
    }

    const insertPi = (node) => {

        let startPosition = node.selectionStart;
        let endPosition = node.selectionEnd;

        node.setRangeText('\\pi', startPosition, endPosition, 'select');

        node.focus()
    }

    const insertTheta = (node) => {

        let startPosition = node.selectionStart;
        let endPosition = node.selectionEnd;

        node.setRangeText('\\theta', startPosition, endPosition, 'select');

        node.focus()
    }

    const insertPlusMinus = (node) => {

        let startPosition = node.selectionStart;
        let endPosition = node.selectionEnd;

        node.setRangeText('\\pm', startPosition, endPosition, 'select');

        node.focus()
    }

    const insertTable = () => {

        if( row && column ){

            editor.commands.insertTable({ rows: row, cols: column, withHeaderRow: true });
        }

        showTableTools = false;
    }

    const addColLeft = () => {

        if( isTableActive ){

            editor.commands.addColumnBefore();
        }

        showTableTools = false;
    }

    const addColRight = () => {

        if( isTableActive ){

            editor.commands.addColumnAfter();
        }

        showTableTools = false;
    }

    const addRowAbove = () => {

        if( isTableActive ){

            editor.commands.addRowBefore()
        }

        showTableTools = false;
    }

    const addRowBelow = () => {

        if( isTableActive ){

            editor.commands.addRowAfter();
        }

        showTableTools = false;
    }

    const delRow = () => {

        if( isTableActive ){

            editor.commands.deleteRow()
        }

        showTableTools = false;
    }

    const delCol = () => {

        if( isTableActive ){

            editor.commands.deleteColumn();
        }

        showTableTools = false;
    }

    const delTable = () => {

        if( isTableActive ){

            editor.commands.deleteTable()
        }

        showTableTools = false;
    }

    const toggleTable = () => showTableTools = ! showTableTools;

    $: {
        if(editor){

            isTableActive = editor.isActive('table');
        }
    }

</script>


    <div class="flex space-x-2 items-center">
        <button type="button"  class={ `text-left w-full rounded-lg cursor-text ${ isOptionEditor ? 'p-2.5' : 'px-4 py-6'}  border-2 border-gray-300`}>
            <div class="space-y-3">
                { #if showTools }
                    <div class="grid grid-cols-4 w-full justify-between border rounded text-gray-700 border-gray-300 uppercase text-xs">
                        <button on:click={ clickImageFile } class={ `${selectedQuestionType === questionTypes.picture ? 'bg-gray-200' : ''} cursor-pointer uppercase p-3 border-r text-center`}>
                            <span>Picture</span>
                        </button>
                        <div class="relative w-full">
                            <button on:click={ toggleTable } class={`cursor-pointer uppercase p-3 border-r text-center w-full`}>
                                <span>Table</span>
                            </button>
                            { #if showTableTools }

                                { #if isTableActive }

                                    <div in:fade="{{ duration: 75 }}"  class="absolute inset-x-0 mt-2 ml-[0.27rem] p-4 isolate z-50 bg-white min-h-full min-w-max max-w-min border rounded-lg">
                                        <div class="grid grid-cols-2 gap-2">
                                        <button on:click={ addColLeft } class="w-full p-2.5 text-xs text-gray-700 text-left hover:bg-gray-100 rounded-lg transition">Insert Col Left</button>
                                        <button on:click={ addColRight } class="w-full p-2.5 text-xs text-gray-700 text-left hover:bg-gray-100 rounded-lg transition">Insert Col Right</button>
                                        <button on:click={ addRowAbove } class="w-full p-2.5 text-xs text-gray-700 text-left hover:bg-gray-100 rounded-lg transition">Insert Row Above</button>
                                        <button on:click={ addRowBelow } class="w-full p-2.5 text-xs text-gray-700 text-left hover:bg-gray-100 rounded-lg transition">Insert Row Below</button>
                                        <button on:click={ delRow } class="w-full p-2.5 text-xs text-gray-700 text-left hover:bg-gray-100 rounded-lg transition">Delete Row</button>
                                        <button on:click={ delCol } class="w-full p-2.5 text-xs text-gray-700 text-left hover:bg-gray-100 rounded-lg transition">Delete Col</button>
                                        <button on:click={  delTable } class="w-full p-2.5 text-xs text-gray-700 text-left hover:bg-gray-100 rounded-lg transition">Delete Table</button>
                                        </div>
        
                                        <div class="flex mt-5 space-x-3 w-full items-center justify-center">
                                            <Button on:click={ () => showTableTools = false } className="bg-gray-200 text-gray-700 focus:ring-gray-100 focus:bg-gray-200 hover:bg-gray-300 w-full"  buttonText="Cancel" />
                                        </div>
                                    </div>

                                { :else }

                                    <div in:fade="{{ duration: 75 }}"  class="absolute inset-x-0 mt-2 ml-[0.27rem] p-4 isolate z-50 bg-white min-h-full w-[14rem] border rounded-lg">
                                        <div class="flex space-x-6">
                                            <div class="">
                                                <Input type="number" on:input={ (e) => row = e.detail.input } className="p-2" label="No. of Rows" labelStyle="text-[10px]" />
                                            </div>

                                            <div class="">
                                                <Input type="number" on:input={ (e) => column = e.detail.input } className="p-2" label="No. of Cols" labelStyle="text-[10px]" />
                                            </div>
                                        </div>

                                        <div class="flex mt-5 space-x-3">
                                            <Button on:click={ () => showTableTools = false } className="bg-gray-200 text-gray-700 focus:ring-gray-100 focus:bg-gray-200 hover:bg-gray-300 max-w-min "  buttonText="Cancel" />
                                            <Button on:click={ insertTable } buttonText="Insert Table" />
                                        </div>
                                    </div>

                                {/if}
                                
                            {/if}
                            
                        </div>
                        
                        <button on:click={ () => setSelectedQuestionType( questionTypes.video ) } type="button" class={ `${selectedQuestionType === questionTypes.video ? 'bg-gray-200' : ''} uppercase p-3 border-r text-center`}>Video</button>
                        <button on:click={ insertFormula } type="button" class={`relative uppercase p-3 text-center`}>
                            <span>Math</span>
                        </button>
                    </div>
                { /if }
            </div>

            <div use:textEditor></div>
        
            { #if canClear } 
                <button class="text-red-500" on:click={ clearContent }>Delete</button>
            { /if }
        </button>
    
        { #if isOptionEditor }
            <button on:click={ clickImageFile } class={`flex items-center shrink-0 justify-center h-11 w-11 border-2 border-gray-300 rounded-lg`}>
                <Icons icon="picture" className="stroke-gray-400" />
            </button>
            <button on:click={ insertFormula } class={`flex items-center shrink-0 justify-center h-11 w-11 border-2 border-gray-300 rounded-lg`}>
                <Icons icon="sigma" className="stroke-gray-400" />
            </button>
        { /if }
    </div>

<input on:input={ addImage } id={ `pic${id}` } type="file"  accept="image/*" hidden>