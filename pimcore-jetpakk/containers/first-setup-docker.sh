#!/usr/bin/env bash

# THIS IS USED FOR CONFIGURING DOCKER IN ***VAGRANT***
# Not relevant for native/local docker usage and giptpod!

if [ ! -d "/etc/docker" ]; then
  # we need to stop ubuntu from auto-updating the kernel for dev systems as there currently is an issue with newer kernerls on Parallels M1
  apt-mark hold linux-generic linux-image-generic linux-headers-generic

  apt-get update && apt-get install -y docker.io
  mkdir -p /etc/systemd/system/docker.service.d/
  cd /etc/systemd/system/docker.service.d/
  cat <<EOF > override.conf
[Service]
ExecStart=
ExecStart=/usr/bin/dockerd -H fd:// --containerd=/run/containerd/containerd.sock -H tcp://0.0.0.0:2375
EOF

  systemctl daemon-reload
  systemctl enable docker.service
  systemctl restart docker.service
  
fi