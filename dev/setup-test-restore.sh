#!/usr/bin/env bash

# Create 3GB swap file
dd if=/dev/zero of=/swapfile bs=1024 count=3048576
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
