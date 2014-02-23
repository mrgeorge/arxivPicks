#!/bin/bash

# Define arxivPicks home directory as current dir
APHOME=`pwd`

# Create bookmark database
cd backend
sqlite arxivPicks.db < makeDB.sql

# Set permissions to database file and directory
chmod o+w $APHOME $APHOME/backend $APHOME/backend/arxivPicks.db

# Replace paths in php scripts
find -name *.php -type f -exec sed -i "s%/path/to/arxivPicks%${APHOME}%g" {} \;
