set -ex
git pull --ff-only

# Ansible script expects webapp.tgz inside ansible directory
rm -f ansible/webapp.tgz
tar -czf ansible/webapp.tgz webapp

ansible-playbook -i "localhost," -c local ansible/play_main.yml --tags copy-webapp