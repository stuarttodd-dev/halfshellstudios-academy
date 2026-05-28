FROM alpine:3.20 AS build
RUN apk add --no-cache build-base git \
 && mkdir -p /out \
 && echo "v1 artifact" > /out/release.txt

FROM alpine:3.20 AS runtime
COPY --from=build /out/release.txt /release.txt
CMD ["cat", "/release.txt"]
