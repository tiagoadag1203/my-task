FROM node:20.19.0-alpine

WORKDIR /var/www/frontend

ENV NUXT_HOST=0.0.0.0
ENV NUXT_PORT=3000

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
