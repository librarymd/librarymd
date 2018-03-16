# Library.md - knowledge for all

This is the opensource version of torrentsmd that comes with an autoinstaller.

## Features

Library.md is a trackerless knowledge sharing site.
It's integrated with dht-client-daemon to fetch the number of peers of torrents from DHT.
Automated import and export of torrents via rest endpoints.

## Stack

Mysql, php 5.6.
Sphinx for search.
Mongodb for torrent tags.
dht-client-daemon for dht peer retrieval.

## Auto-installer

Auto-installer is based on ansible.
It was tested on Ubuntu 16.x and 17.x.
A user the name "ubuntu" with must exist, some cron jobs will be added to it.
You need to run one command to install and configure everything.
Run it as root or a sudo enabled user.

```
./install-all.sh
```

## Local development with vagrant

If you want to test locally this version, you can install the app in a VM using virtualbox & vagrant.

Install [vagrant](https://www.vagrantup.com/docs/installation/) and [Virtualbox](https://www.virtualbox.org/wiki/Downloads).
Familiarize yourself with ansible/Vagrantfile to see the ports that are redirected.

```
cd ansible
vagrant up
ssh-add ansible/.vagrant/machines/default/virtualbox/private_key
ansible-playbook -i ansible/hosts ansible/play_main.yml
```

## Update scripts after install
If you want to use the latest webapp version after a previous version was installed.

./update-code.sh to copy files from local webapp to /www folder.

NOTE: The evolution of schema is currently not supported.

## Replication between trackers
Information about the torrents can be replicated across the trackers using scripts inside periodic/import/ folder. 

- torrents_export.php will export torrents information in JSON format.
- torrents_import.php and torrents_import_poll_last.php importing the latest torrents.
- torrents_import_images.php import the images.

## Create an admin account on the website
Simply register an account and ssh to machine and execute the following query:

- mysql "UPDATE webapp.users SET class=10 WHERE id=$your_user_id"
