#!/bin/bash
# Create a backup for day and compress
pg_dump -d servicios_publicos > "servicios_publicos_tmp.sql" && \
tar -zcvf "servicios_publicos_$(date +%y-%m-%d).sql.tar.gz" "servicios_publicos_tmp.sql" && \
rm "servicios_publicos_tmp.sql"