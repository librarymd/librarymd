set -ex

# Ansible script expects webapp.tgz inside ansible directory
rm -f ansible/webapp.tgz
tar -czf ansible/webapp.tgz webapp

ansible/ubuntu-bootstrap-ansible.sh
ansible-playbook -i "localhost," -c local ansible/play_main.yml

# Optional but highly encouraged
ansible-playbook -i "localhost," -c local ansible/play_config_firewall.yml