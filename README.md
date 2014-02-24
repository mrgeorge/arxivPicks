arxivPicks
==========
Browser extension and website to bookmark papers from [arxiv.org](http://arxiv.org)

arxivPicks is a set of tools to seamlessly bookmark papers for group discussions. A JavaScript browser extension is built upon [Greasemonkey](http://www.greasespot.net) (Firefox) or [Greasekit](http://8-p.info/greasekit/) (Safari) to modify arxiv.org paper listings in place with a bookmark button (see screenshots/ directory for a couple of examples). When the button is pressed, a request is posted to a self-hosted PHP script, which retrieves the paper metadata from the arxiv.org API, parses the feed with SimplePie and stores it in a local SQLite database. A self-hosted website then displays the recent list of bookmarked papers, sorted by the number of votes received and the date. (See [astro.berkeley.edu/~mgeorge/arxiv](http://astro.berkeley.edu/~mgeorge/arxiv) for a working example used at the UC Berkeley Astronomy Department.)

Installation
------------
Clone the repository to the machine where you will host the database and website

    git clone git@github.com:mrgeorge/arxivPicks.git

Run the setup script and follow the prompts

    cd arxivPicks
    ./setup.sh

The database, browser extension scripts, and web files are now set up on your machine. To run the extension use must open it with Greasemonkey (Firefox) or Greasekit (Safari). For Firefox, you can also create a stand-alone extension (.xpi file) with the script compiler at https://arantius.com/misc/greasemonkey/script-compiler.php .

The default discussion times are Tuesday and Thursday mornings, and a cron job can be set up to run the email scripts in arxivPicks/backend. You can modify the times there as well as in the website files prior to running the setup script.

Dependencies
------------
* PHP, SQLite, bash for backend
* Firefox or Safari with Greasemonkey/Greasekit for browser extension

Future Work
-----------
Extend to other browsers including Chrome.

Customize meeting times in setup script.

Improve input sanitization for security. (No warranty is implied with this code.)

History
-------
James Graham at Cambridge made the initial Python and JavaScipt implementation in 2008, which was translated and extended by Matt George at Berkeley in 2009-2010. Lightly modified for github in 2014. The style sheet for the paper list page was stolen from arxiv.org, with the header color scheme appropriately changed.
