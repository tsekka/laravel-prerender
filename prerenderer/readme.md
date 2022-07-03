# Prerequisites for server
1. Install node and npm.
2. Install headless chrome and it's dependecies. For example:
``` bash
# Install required packages. 
# Example for ubuntu 20.04 (should also work for other debian-based distributions ):
sudo apt-get install -y libappindicator1 fonts-liberation
sudo apt-get install -f
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo dpkg -i google-chrome*.deb

# Check google chrome version:
google-chrome-stable -version

# If checking google chrome version gives you an error, then try:
 - sudo apt --fix-broken install
 - sudo apt install libnss3
 - restarting server or 
 - installing different node version.
```
3. *optional* Make sure the prerenderer's service keeps working even after reboot. One option is to use supervisor. Example (assuming your server has supervisor already installed):
   * In `/etc/supervisor/conf.d` create new conf file with following content:
   ```
   [program:prerender_worker]
   command=/usr/bin/npm start --prefix PATH_TO_DIRECTORY/prerenderer
   numprocs=1
   autostart=true
   autorestart=true
   user=NON_ROOT_USERNAME
   stdout_logfile=PATH_TO_DIRECTORY/log-prerender-worker.log
   ```
   * run `supervisorctl stop all && supervisorctl reread && supervisorctl start all` as root user
   * Node must be installed globally to run it with supervisor.
   * If process fails to start, try updating npm: npm install -g npm

# Install dependencies
`npm install`

# Start server
Run the server `node server.js` or `ALLOWED_DOMAINS="yoursite.com,yoursite.org" node server.js` to enable requests for specific domains only. Make sure to restart the service if server reboots.

# Check if prerenderer is working
- Run command `curl http://yourdomain.com:3000/render?url=https://www.example.com/`
- or visit the same url on browser.

# Links
<a href="https://github.com/prerender/prerender#prerenderio">Prerender documentation</a>
