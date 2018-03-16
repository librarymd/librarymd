Application configuration

1. edit include/secrets.php and change:
* $application_secret
* $mysql_*
* $sphinx_*

Check for ansible in perso/playground/ansible.

Search is using sphinx, the configuration is in: /etc/sphinx/sphinx.conf

2.
ansible-playbook -i ~/perso/playground/ansible/hosts play1.yml -vvv --tags php

## Start sphinx localy

Read docs/sphinx.md