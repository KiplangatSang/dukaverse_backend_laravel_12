<?php
namespace App\Http\Controllers;

use App\Models\Task;

class TaskController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $tasks = $this->account()
            ->tasks()
            ->with('assignees')
            ->with('dependencies')
            ->with('taskable')
            ->with("comments")
            ->get();

        if (! $tasks) {
            return $this->sendError("Bad request", ["error" => "The tasks could not be fetched.", "result" => $tasks]);
        }

        return $this->sendResponse(["tasks" => $tasks], "success, The tasks has been fetched successfully.");

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $task_priorities = Task::TASK_PRIORITIES;
        $projects        = $this->account()->projects()->with('teams.members')->with('tasks')->get();
        $campaigns       = $this->account()->projects()->with('teams.members')->with('tasks')->get();

        $task_dependencies = TaskDependency::TASK_DEPENDENCIES;

        if (! $task_priorities) {
            return $this->sendError("Bad request", ["error" => "The task fields could not be fetched.", "result" => $task_priorities]);
        }

        return $this->sendResponse(["task_priorities" => $task_priorities,
            "projects"                                    => $projects,
            "campaigns"                                   => $campaigns,
            "task_dependencies"                           => $task_dependencies], "success, The task fields has been fetched successfully.");

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "user_id"      => ['sometimes', 'exists:users,id'],
                "name"         => ['required'],
                "start_date"   => ['required'],
                "end_date"     => ['required'],
                "progress"     => ['required'],
                "dependencies" => ['sometimes'],
                "priority"     => ['required'],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The task could not be saved .", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();

        $task = null;
        if ($request->project_id) {
            $task = $this->account()->tasks()->create(
                [
                    "taskable_id"   => $request->project_id,
                    "taskable_type" => Project::class,
                    "user_id"       => auth()->id(),
                    "name"          => $validatated["name"],
                    "start_date"    => $validatated["start_date"],
                    "end_date"      => $validatated["end_date"],
                    "progress"      => $validatated["progress"],
                    "priority"      => $validatated["priority"],

                ]
            );

        }
        if ($request->campaign_id) {
            $task = $this->account()->tasks()->create(
                [
                    "taskable_id"   => $request->campaign_id,
                    "taskable_type" => Campaign::class,
                    "user_id"       => auth()->id(),
                    "name"          => $validatated["name"],
                    "start_date"    => $validatated["start_date"],
                    "end_date"      => $validatated["end_date"],
                    "progress"      => $validatated["progress"],
                    "priority"      => $validatated["priority"],

                ]
            );

        }

        $dependencyErrors = [];
        if ($request->dependencies) {
            $dependencies = json_decode($request->dependencies);
            foreach ($dependencies as $dependency) {

                $validator = Validator::make([$dependency->dependency => "task_dependency"],
                    [
                        "task_dependency" => ['exists:tasks,id'],
                    ]);

                if ($validator->fails()) {
                    return $validator->errors();
                    array_push($dependencyErrors, $validator->errors());
                }

                $created_task_dependency = $task->dependencies()->create(
                    ["taskable_id"    => $task->taskable_id,
                        "taskable_type"   => $task->taskable_type,
                        "project_id"      => $request->project_id,
                        "depends_on"      => $dependency->dependency,
                        "dependency_type" => $dependency->key]
                );

            }

        }

        $task = Task::where('id', $task->id)->with('project')->with('dependencies')->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The  task could not be saved.", "result" => $task, "dependencyErrors" => $dependencyErrors]);
        }

        return $this->sendResponse(["task" => $task, "dependencyErrors" => $dependencyErrors], "success, The  task has been saved successfully.");

    }

    /**
     * Store subtasks.
     */
    public function storeSubTasks(Request $request)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "task_id" => ['required', 'exists:projects,id'],
                "user_id" => ['sometimes', 'exists:users,id'],
                "name"    => ['required'],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The task could not be saved .", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();
        $parent_task = Task::where('id', $request->task_id)->first();

        $task = $this->account()->tasks()->create(
            [
                "parent_id"      => $request->task_id,
                "ownerable_id"   => $parent_task->ownerable_id,
                "ownerable_type" => $parent_task->ownerable_type,
                "taskable_id"    => $parent_task->taskable_id,
                "taskable_type"  => $parent_task->taskable_type,
                "user_id"        => $request->user_id ?? auth()->id(),
                "name"           => $validatated["name"],
            ]
        );

        $dependencyErrors = [];
        if ($request->dependencies) {
            $dependencies = json_decode($request->dependencies);
            foreach ($dependencies as $dependency) {

                $validator = Validator::make([$dependency->dependency => "task_dependency"],
                    [
                        "task_dependency" => ['exists:tasks,id'],
                    ]);

                if ($validator->fails()) {
                    return $validator->errors();
                    array_push($dependencyErrors, $validator->errors());
                }

                $created_task_dependency = $task->dependencies()->create(
                    ["taskable_id"    => $task->taskable_id,
                        "taskable_type"   => $task->taskable_type,
                        "taskable_id"     => $request->project_id,
                        "depends_on"      => $dependency->dependency,
                        "dependency_type" => $dependency->key]
                );

            }

        }

        $task = Task::where('id', $task->id)->with('project')->with('dependencies')->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The  task could not be saved.", "result" => $task, "dependencyErrors" => $dependencyErrors]);
        }

        return $this->sendResponse(["task" => $task, "dependencyErrors" => $dependencyErrors], "success, The  task has been saved successfully.");

    }

    /**
     * Display the specified resource.
     */
    public function show($task)
    {
        //

        $task = $this->account()->tasks()->where('id', $task)
            ->with('assignees')
            ->with('dependencies.task')
            ->with('dependencies.dependedTask')
            ->with('taskable')
            ->with('subTasks')
            ->with('comments.user')
            ->with("comments.replies.user")
            ->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The   task could not be found.", "result" => $task]);
        }

        return $this->sendResponse(["task" => $task], "success, The task has been fetched successfully.");

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //

        $task_priorities = Task::TASK_PRIORITIES;
        if (! $task_priorities) {
            return $this->sendError("Bad request", ["error" => "The task fields could not be fetched.", "result" => $task_priorities]);
        }

        $task = $this->account()->tasks()->where('id', $task)->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The   task could not be found.", "result" => $task]);
        }

        return $this->sendResponse(["task" => $task, "task_priorities" => $task_priorities], "success, The task has been fetched successfully.");

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $task)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "project_id"   => ['sometimes', "exists:projects,id"],
                "campaign_id"  => ['sometimes', "exists:campaigns,id"],
                "name"         => ['sometimes', "string"],
                "start_date"   => ['sometimes'],
                "end_date"     => ['sometimes'],
                "progress"     => ['sometimes'],
                "dependencies" => ['sometimes'],
                "priority"     => ['sometimes'],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The task could not be saved .", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();

        $task = $this->account()->tasks()->where('id', $task)->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The   task could not be found.", "result" => $task]);
        }

        if ($request->status) {
            $result = $task->update(
                [

                    "status" => $request->status,
                ]
            );

        }
        $result = $task->update(
            [
                "name"         => $request->name ?? $task->name,
                "start_date"   => $request->start_date ?? $task->start_date,
                "end_date"     => $request->end_date ?? $task->end_date,
                "progress"     => $request->progress ?? $task->progress,
                "dependencies" => $request->dependencies ?? $task->dependencies,
                "priority"     => $request->priority ?? $task->priority,
                "status"       => $request->status ?? $task->status,
            ]
        );

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The  task could not be updated.", "result" => $result]);
        }

        $task = $this->account()->tasks()->where('id', $task->id)->first();

        return $this->sendResponse(["task" => $task], "success, The  task has been updated successfully.");

    }

    /**
     * Assign task to the team members.
     */
    public function assignTask(Request $request, $task)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "assignees" => ['required'],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The task could not be saved .", "result" => $validator->errors()]);
        }

        $task = $this->account()->tasks()->where('id', $task)->first();

        if (! $task) {
            return $this->sendError("Bad request", ["error" => "The   task could not be found.", "result" => $task]);
        }

        $assignees = json_decode($request->assignees);
        $result    = $task->assignees()->syncWithoutDetaching($assignees);

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The  task could not be updated.", "result" => $result]);
        }

        $task = $this->account()->tasks()->where('id', $task)->first();

        return $this->sendResponse(["task" => $task], "success, The  task has been updated successfully.");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($task)
    {
        //
        $result = Task::destroy($task);
        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The  task could not be deleted.", "result" => $result]);
        }

        return $this->sendResponse(["result" => $result], "success, The  task has been deleted successfully.");

    }
}
