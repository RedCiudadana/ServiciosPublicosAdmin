
Requistes:
  - Install supservisor


Add program to supervisor, example (this should be in supervisor configuration file, example: `/etc/supervisor.conf`):
```
[program:admin.tramites.worker.emails]
command=/usr/bin/php7.4 /srv/web-apps/admin.tramites.redciudadana.org/current/bin/console messenger:consume async --limit=100              ; the program (relative uses PATH, can take args)
numprocs=2
startsecs=0
autostart=true
autorestart=true
startretries=10
process_name=%(program_name)s_%(process_num)02d
```
