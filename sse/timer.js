import express from 'express';
import cors from 'cors';
import https from 'http2';
import fs from 'fs';
import knex from 'knex';
import dotenv from 'dotenv';
import moment from 'moment';
import http2Express from 'http2-express-bridge';


dotenv.config();

const app = http2Express(express);

const sslServer = https.createSecureServer( {
    key: fs.readFileSync('./certs/localhost.key' ),
    cert: fs.readFileSync('./certs/localhost.crt' ),
    allowHTTP1: true
}, app);


const db = knex({
    client: 'mysql2',
    version: '8.0',
    connection: {
        host: process.env.DB_HOST,
        port: process.env.DB_PORT,
        user: process.env.DB_USERNAME,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_DATABASE
    }
});

app.use( cors({ credentials: false, origin: '*'}) );

app.get('/sse', async (req, res) => {

    res.setHeader('Content-Type', 'text/event-stream');
    res.setHeader('Cache-Control', 'no-store');
    res.setHeader('Connection', 'keep-alive');


    let subjectId = req.query.subjectId;
    let assessmentId = req.query.assessmentId;
    let studentId = req.query.studentId;

    try{

        let studentSession = await db.table('assessment_results').where({'student_profile_id' : studentId, 'assessment_id': assessmentId, 'subject_id' : subjectId }).first();

        let endTime = moment(studentSession.end_time).unix();
        let startTime = moment().add(1, 'hour').unix();

        let timeRemaining = endTime - startTime;

        let timer = setInterval(async () => {

            timeRemaining --;

            if( timeRemaining == 0 ) {

                clearInterval(timer);

                res.write(`data: ${timeRemaining}\n\n`);

                res.end();

                return;
            }

            await db.table('assessment_results').where({'student_profile_id' : studentId, 'assessment_id': assessmentId, 'subject_id' : subjectId }).limit(1).update({ time_remaining: timeRemaining });

            res.write(`data: ${timeRemaining}\n\n`);

        }, 1000 );


    }catch(err){

        console.log(err)

    }
    
});


sslServer.listen(8080, (e) => {

    if( e ){
        console.log(e)
    }else{
        console.log('Listening on port 8080')
    }
});