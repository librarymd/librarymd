set -e
set -x

# Misc install

sudo apt-get update -y
sudo apt-get install -y ntp zip

sudo service ntp restart
sudo ntpq -c lpeer

# Ansible install

sudo apt-get install -y python-pip

sudo pip install --upgrade setuptools pip
sudo apt-get install -y python-pip
sudo apt-get install -y software-properties-common
sudo apt-add-repository -y ppa:ansible/ansible
sudo apt-get update -y
sudo pip install ansible==2.4.2.0

ansible --version
ansible-playbook --version