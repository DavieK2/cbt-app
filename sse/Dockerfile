FROM node:18.19.0-alpine

RUN mkdir -p /home/node/app && chown node:node /home/node/app

WORKDIR /home/node/app

USER node

COPY --chown=node:node ./package*.json ./

RUN npm i

EXPOSE 8080

CMD [ "node", "timer.js" ]