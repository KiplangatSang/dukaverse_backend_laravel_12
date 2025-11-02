# TODO: Link Job Applications with Calendars and Tasks for Interview Scheduling

## Current Status
- [x] Analyze codebase and create plan
- [x] Get user approval for plan
- [x] Create database migration for calendar_id and task_id columns in job_applications table
- [x] Update JobApplication model to add calendar_id, task_id fields and relationships
- [x] Modify JobController updateApplicationStatus method to create calendar event and task when status becomes 'interviewed'
- [x] Add selectInterviewDate method in JobController for applicants to select interview dates
- [x] Update routes/api.php to add new interview date selection endpoint

## Pending Tasks
- [x] Handle calendar conflicts and availability logic
- [ ] Test the integration and ensure proper notifications for team members
