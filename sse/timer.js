import express from 'express';
import cors from 'cors';
import https from 'http2';
import fs from 'fs';
import knex from 'knex';
import dotenv from 'dotenv';
import moment from 'moment';
import http2Express from 'http2-express-bridge';
import Redis from 'ioredis'



dotenv.config();

const app = http2Express(express);


const redis = new Redis({
    host: process.env.REDIS_HOST,
    port: process.env.REDIS_PORT,
    db: 0
});


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

app.use( cors({ credentials: false, origin: '*' }) );

app.get('/sse', async (req, res) => {

    res.writeHead(200, {
        'Content-Type': 'text/event-stream',
        'Cache-Control' : 'no-cache',
        'Connection': 'keep-alive'
    });

    res.flushHeaders();

    let subjectId = req.query.subjectId;
    let assessmentId = req.query.assessmentId;
    let studentId = req.query.studentId;


    try{

        let studentSession = await db.table('assessment_results').where({'student_profile_id' : studentId, 'assessment_id': assessmentId, 'subject_id' : subjectId }).first();

        let endTime = moment( studentSession.end_time ).unix();
        let startTime = moment().add( 1, 'hour' ).unix();

        let timeRemaining = ( endTime - startTime );

        redis.subscribe(`soncal_database_add-time`, `soncal_database_add-time-${studentId}`);

        redis.on("message", (channel, message) => {

            if( channel === `soncal_database_add-time-${studentId}` ){

                timeRemaining = timeRemaining + parseInt(message);

                console.log(message)
            }

            if( channel === `soncal_database_add-time` ){

                timeRemaining = timeRemaining + parseInt(message);
            }
                    
        });


        let timer = setInterval( async () => {

            timeRemaining --;

            if( timeRemaining == 0 ) {

                
                res.write(`data: ${timeRemaining}\n\n`);
                
                res.end();
                
                redis.unsubscribe();
                
                clearInterval(timer);

                return;
            }

            res.write(`data: ${timeRemaining}\n\n`);

        }, 1000 );


        res.on("close", async () => {

            await db.table('assessment_results').where({'student_profile_id' : studentId, 'assessment_id': assessmentId, 'subject_id' : subjectId }).limit(1).update({ time_remaining: timeRemaining });
            
            res.end();
            
            redis.unsubscribe();

            clearInterval(timer);
        })


    }catch(err){

        console.log(err)

    }
    
});

sslServer.setMaxListeners(20);

sslServer.listen( 8080, (e) => {

    if( e ){
       
        console.log(e)

    }else{
        
        console.log('Listening on port 8080')
    }
});