#!/bin/bash

# Define arxivPicks home directory as current dir
APHOME=`pwd`

# Create bookmark database
cd backend
sqlite arxivPicks.db < makeDB.sql
cd -

# Set permissions to database file and directory
chmod o+w $APHOME $APHOME/backend $APHOME/backend/arxivPicks.db

# Replace paths in php scripts
find $APHOME/ -name \*.php -type f -exec sed -i "s%/path/to/arxivPicks%${APHOME}%g" {} \;

# Prompt for URL
read -e -p "Enter URL for public website: " -i "http://example.com/arxivPicks" APURL
# Replace URL in browser extension
find $APHOME/extension/*.user.js -type f -exec sed -i "s%http://example.com%${APURL}%g" {} \;

# Prompt for email address
read -e -p "Enter email address for discussion list: " -i "everyone@example.com" APEMAIL
# Replace address in mail script
find $APHOME/backend/*mail.php -type f -exec sed -i "s%everyone@example.com%${APEMAIL}%g" {} \;

# Prompt for local web dir
read -e -p "Enter path to local dir for public web files: " -i "$HOME/public_html/arxivPicks" APWEBDIR
if [ ! -d "$APWEBDIR" ]; then
    mkdir -p -v APWEBDIR
fi
# Move public web files to web dir
rsync -avz $APHOME/website/ $APWEBDIR
rm -rf $APHOME/website

echo "================================================================================"
echo "arxivPicks setup has run."
echo "Database initialized in ${APHOME}/backend/arxivPicks.db"
echo "Web files moved to ${APWEBDIR} with assumed URL ${APURL}"
echo "To do: -add cron job to run mail scripts in ${APHOME}/backend"
echo "       -install user scripts in ${APHOME}/extension with Greasemonkey (Firefox) or Greasekit/SIMBL (Safari)"
echo "        or convert to .xpi (Firefox) at https://arantius.com/misc/greasemonkey/script-compiler.php"
echo "================================================================================"
