# Pin the base image digest so builds do not silently pick up a moved tag.
# Refresh the hash when you intentionally upgrade Alpine — see README.
FROM alpine:3.20@sha256:d9e853e87e55526f6b2917df91a2115c36dd7c696a35be12163d44e6e2a4b6bc
RUN echo "built on pinned base" > /msg.txt
CMD ["cat", "/msg.txt"]
