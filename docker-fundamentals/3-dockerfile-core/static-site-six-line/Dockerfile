FROM nginx:stable-alpine

WORKDIR /usr/share/nginx/html

# Remove stock welcome page so only our assets are served.
RUN rm -f ./*

COPY index.html ./
COPY styles.css ./

EXPOSE 80
