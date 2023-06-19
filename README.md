## About e-Performance Management System (ePMS)

The ePMS is an information system aimed to manage all phases of the Strategic Performance Management System: Performance Planning, Coaching and Mentoring, Performance Review and Feedback and Performance Rewarding and Development Planning. ePMS will provide features to allow creation and storage of performance management documents, the conduct of the assessment of a unit or staff performance vis-à-vis set targets, conduct coaching and mentoring, plan performance rewards and faculty development, and generate reports such as ranking of units, scorecards, coaching and monitoring journals, faculty development plan, amongst others.

## Benefits

- Eliminates paper-based execution of performance management
- To ensure the documents are monitored and controlled in each phase of Performance
Management
- To evaluate offices, colleges, units and non-teaching personnel in terms of timeliness,
effectiveness and efficiency of documents
- To track documents whether submitted or returned by the respective office, unit, college
or the non-teaching personnel

## Modules

- Performance Planning (On Development)
- Performance Review and Feedback (For Development)
- Coaching and Mentoring (For Development)
- Performance Rewarding and Development Planning (For Development)

# SETUP

### UNIT SETUP
#### Install xampp 7.4.7
https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.4.7/

#### Install Git (version 2.31.1 32 bit)
https://github.com/git-for-windows/git/releases

#### Install Composer
https://github.com/composer/composer/releases

#### Install nodejs (version 14.15.5)
https://nodejs.org/en/download/releases/

#### Install JDK (version 1.7 Update 80 64bit)
___________
Next, go to xampp\tomcat\webapps

#### CONFIG TOMCAT PORT
1. usually "C:\xampp\tomcat\conf"
2. open server.xml
3. search 8080
4. replace it with 8090 or any other port you want

#### config php.ini
allow_url_fopen = On
allow_url_include = On

#### Create db 
* charset - utf8mb4
* collation - utf8m4_unicode_ci

#### Clone repository
1. Open terminal
2. Run
   1. `git clone https://github.com/SDMD-Repos/usep-epms.git`
   2. `cd usep-epms/backend`
   3. `composer install`
   4. `npm install`
   5. `cd ../frontend`
   6. `npm install`
3. Open repository in File Explorer 
4. Open backend folder 
5. Copy and paste .env.example 
6. Rename the copied to .env\
_NOTE: do also in frontend folder_
7. Run commands
   1. `cd ../backend`
   2. `php artisan key:generate`
8. Open repo in editor
9. Find backend/.env
10. Update all that applies 
    * HRIS_API_TOKEN - _used for HRIS login API_ 
    * DATA_HRIS_API_TOKEN - _used for all HRIS endpoint except login_ 
    * MASTER_PASSWORD= _indicate the app master password_
11. Find frontend/.env 
12. Update all that applies 
    * VUE_APP_BACKEND_URL - API endpoint's host 

#### Database setup
1. Start MySQL in XAMPP Control Panel
2. Go to root project and open terminal
3. Run Commands
   * `cd backend`
   * `php artisan migrate`
   * `composer dump-autoload`
   * `php artisan db:seed`
   * `php artisan passport:install`
   * `php artisan storage:link`\
   _NOTE: Double check if directory exists `public/storage/uploads`_

#### PDF Viewer setup
Change localhost to `usepepms.local. _Refer to the link below for the steps_\
https://stevencotterill.com/snippets/vue-cli-using-a-custom-domain

#### Run applications (frontend and backend)
1. Open terminal
2. Run commands
   * `php artisan serve`
   * `cd ../frontend` 
   * `npm run serve`

_For deployment_
1. Run commands
   * `npm run build`
   * `serve -l tcp`