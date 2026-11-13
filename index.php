<?php
require_once 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $taskId = (int) ($_POST['task_id'] ?? 0);

    // CREATE TASK
    if ($action === 'create') {

        $taskText = trim($_POST['task'] ?? '');
        $taskDescription = trim($_POST['task_description'] ?? '');

        if (
            $taskText !== '' &&
            strlen($taskText) <= 120 &&
            $taskDescription !== ''
        ) {
            $stmt = $pdo->prepare(
                "INSERT INTO tasks (task_name, task_description)
                 VALUES (?, ?)"
            );

            $stmt->execute([$taskText, $taskDescription]);
        }
    }

    // UPDATE TASK
    elseif ($action === 'update') {

        $taskText = trim($_POST['task'] ?? '');
        $taskDescription = trim($_POST['task_description'] ?? '');

        if (
            $taskId > 0 &&
            $taskText !== '' &&
            strlen($taskText) <= 120 &&
            $taskDescription !== ''
        ) {
            $stmt = $pdo->prepare(
                "UPDATE tasks
                 SET task_name = ?, task_description = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $taskText,
                $taskDescription,
                $taskId
            ]);
        }
    }

    // DELETE TASK
    elseif ($action === 'delete') {

        if ($taskId > 0) {
            $stmt = $pdo->prepare(
                "DELETE FROM tasks WHERE id = ?"
            );

            $stmt->execute([$taskId]);
        }
    }

    // TOGGLE TASK STATUS
    elseif ($action === 'toggle') {

        if ($taskId > 0) {
            $stmt = $pdo->prepare(
                "UPDATE tasks
                 SET status = IF(
                     status = 'pending',
                     'completed',
                     'pending'
                 )
                 WHERE id = ?"
            );

            $stmt->execute([$taskId]);
        }
    }

    // Redirect after POST
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// READ TASKS FROM DATABASE
$stmt = $pdo->query(
    "SELECT * FROM tasks ORDER BY created_at DESC"
);

$tasks = $stmt->fetchAll();

// CALCULATE TASK STATISTICS
$totalTasks = count($tasks);

$completedTasks = 0;

foreach ($tasks as $task) {
    if ($task['status'] === 'completed') {
        $completedTasks++;
    }
}

$activeTasks = $totalTasks - $completedTasks;

$progressPercent = $totalTasks > 0
    ? round(($completedTasks / $totalTasks) * 100)
    : 0;
?>




<!DOCTYPE html>
<html lang="en">
  <!-- external CSS / JavaScript files. -->
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="A simple, pink task manager. One thing at a time."
    />
    <title>Petal — My tasks</title>
    <link rel="stylesheet" href="style.css" />
    <script src="script.js" defer></script>
  </head>

  <body>
    <!-- Header: the brand link jumps to the page heading. -->
    <header class="site-header">
      <a class="brand" href="#page-title" aria-label="Petal home">
        <img class="brand-icon" src="asset/icon.svg" alt="" width="35" height="35" />
        petal<span class="brand-dot">.</span>
      </a>
      <span class="header-caption">A little space to get things done.</span>
    </header>


    <main class="workspace">
      <!-- Introductory heading and short description. -->
      <section class="intro" aria-labelledby="page-title">
        <p class="eyebrow">YOUR EVERYDAY TASK MANAGER</p>
        <h1 id="page-title">Small steps.<br /><em>A clearer day.</em></h1>
        <p>
          Make a list, take your time, and check things off.<br class="desktop-break" />
          One task at a time is enough.
        </p>
      </section>

      <!-- Desktop: two columns. Mobile: the form appears above the task list. -->
      <div class="workspace-grid">
        <!-- Task composer: the same form is used to add and edit tasks. -->
        <aside class="composer panel" aria-labelledby="composer-title">
          <span class="section-number">MAKE A LITTLE PLAN</span>
          <h2 id="composer-title">What’s next?</h2>
          <p class="muted">Give your next task a place to start.</p>

          <!-- UI-only: JavaScript previews the form. Add PHP POST processing separately. -->
          <form id="task-form" method="POST" action="">
            <!-- These fields provide action and task ID hooks for future PHP handling. -->
            <input type="hidden" name="action" id="form-action" value="create" />
            <input type="hidden" name="task_id" id="task-id" value="" />

            <label for="task-input">Task name <span aria-hidden="true">*</span></label>
            <input
              type="text"
              id="task-input"
              name="task"
              placeholder="e.g. Review web development notes"
              maxlength="120"
              required
              aria-describedby="input-help form-error"
              autocomplete="off"
            />

            <div class="input-meta">
              <span id="input-help">Keep it simple and specific.</span>
              <span id="char-count">0/120</span>
            </div>

            <label for="task-description">Task description <span aria-hidden="true">*</span></label>

              <textarea
                  id="task-description"
                  name="task_description"
                  placeholder="Add a little more detail about your task..."
                  rows="3"
                  required
              ></textarea>


            <p id="form-error" class="error" role="alert"></p>

            <button type="submit" class="primary" id="submit-button">
              <span aria-hidden="true">＋</span> Add task
            </button>
            <button type="button" class="cancel" id="cancel-edit" hidden>
              Cancel editing
            </button>
          </form>

          <div class="gentle-note">
            <img class="note-flower" src="asset/icon.svg" alt="" width="32" height="32" />

            <p>
              You don’t have to do it all.<br />
              <strong>Just start with one thing.</strong>
            </p>
          </div>
        </aside>

        <!-- Task board: JavaScript renders sample task rows into the empty list. -->
        <section class="task-board panel" aria-labelledby="list-title">
          <div class="board-heading">
            <div>
              <span class="section-number">ONE THING AT A TIME</span>
              <h2 id="list-title">My tasks <span id="total-badge" class="badge"><?php echo $totalTasks; ?></span></h2>
            </div>

            <img class="small-flower" src="asset/icon.svg" alt="" width="39" height="39" />
          </div>

          <div class="list-toolbar">
            <div class="filters" role="group" aria-label="Filter tasks">
              <button type="button" data-filter="all" aria-pressed="true">
                All <span id="all-count"><?php echo $totalTasks; ?></span>
              </button>
              <button type="button" data-filter="active" aria-pressed="false">
                Active <span id="active-count"><?php echo $activeTasks; ?></span>
              </button>
              <button type="button" data-filter="completed" aria-pressed="false">
                Completed <span id="completed-count"><?php echo $completedTasks; ?></span>
              </button>
            </div>
          </div>

         <ul id="task-list" class="task-list" aria-label="Tasks">

                <?php foreach ($tasks as $task): ?>

                <?php $isCompleted = $task['status'] === 'completed'; ?>

                <li class="task-row <?php echo $isCompleted ? 'is-complete' : ''; ?>">

                    <!-- TOGGLE FORM -->
                    <input
                        type="checkbox"
                        class="task-check"
                        id="task-<?php echo (int) $task['id']; ?>"
                        <?php echo $isCompleted ? 'checked' : ''; ?>
                        form="toggle-form-<?php echo (int) $task['id']; ?>"
                        onchange="this.form.submit()"
                        aria-label="Mark task as completed"
                    />

                    <form
                        id="toggle-form-<?php echo (int) $task['id']; ?>"
                        method="POST"
                        hidden
                    >
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="task_id" value="<?php echo (int) $task['id']; ?>">
                    </form>

                    <!-- TASK TEXT -->
                    <div class="task-content">
                        <span class="task-text">
                            <?php echo htmlspecialchars($task['task_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>

                        <p class="task-description">
                            <?php echo htmlspecialchars($task['task_description'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>

                    <!-- TASK ACTIONS -->
                    <div class="task-actions">

                        <!-- EDIT BUTTON -->
                        <button
                            type="button"
                            class="icon-button"
                            data-action="edit"
                            data-id="<?php echo (int) $task['id']; ?>"
                            data-text="<?php echo htmlspecialchars($task['task_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-description="<?php echo htmlspecialchars($task['task_description'], ENT_QUOTES, 'UTF-8'); ?>"
                            aria-label="Edit task"
                            title="Edit task"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m14 5 5 5M4 20l5-1L20 8a2 2 0 0 0-5-5L4 14z"/>
                            </svg>
                        </button>

                        <!-- DELETE FORM -->
                        <form method="POST" class="delete-form">

                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="task_id" value="<?php echo (int) $task['id']; ?>">

                            <button
                                type="submit"
                                class="icon-button"
                                aria-label="Delete task"
                                title="Delete task"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7"/>
                                </svg>
                            </button>

                        </form>

                    </div>

                </li>

            <?php endforeach; ?>

        </ul>

          <div id="empty-state" class="empty-state" hidden>
            <img class="empty-flower" src="asset/icon.svg" alt="" width="34" height="34" />

            <h3>A little breathing room.</h3>
            <p id="empty-message">Add your first task to get started.</p>
          </div>

          <div class="board-footer">
            <span id="remaining-count">
                <?php
                    echo $activeTasks . ' ' .
                        ($activeTasks === 1 ? 'task' : 'tasks') .
                        ' left to do';
                ?>
            </span>
            <span>Little by little.</span>
          </div>
        </section>
      </div>

      <!-- Summary values are recalculated after each task change. -->
      <section class="progress-panel" aria-label="Task progress">
        <span class="progress-icon" aria-hidden="true">✓</span>

        <div class="progress-copy">
          <h2>Your progress, at a glance</h2>
          <p id="progress-caption">
                <?php echo $completedTasks; ?> of <?php echo $totalTasks; ?> tasks completed.
                <?php echo $activeTasks > 0 ? 'Keep going!' : ($totalTasks > 0 ? 'Enjoy your little win!' : 'A fresh start. Add a task whenever you’re ready.'); ?>
            </p>

        </div>

        <div class="progress-meter">
          <div class="progress-meta">
            <span>Today’s little wins</span>
            <strong id="progress-percent"><?php echo $progressPercent; ?>%</strong>
          </div>

            <progress
                id="progress-bar"
                value="<?php echo $completedTasks; ?>"
                max="<?php echo $totalTasks > 0 ? $totalTasks : 1; ?>"
            >
                <?php echo $progressPercent; ?>%
            </progress>


        </div>
      </section>

      <!-- Announce changes to assistive technology without adding visible text. -->
      <p id="announcement" class="sr-only" role="status" aria-live="polite"></p>

      <noscript>
        <p class="noscript">Enable JavaScript to use task editing, filters, and completion toggles.</p>
      </noscript>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
      <span>© 2026 petal. <span class="footer-divider">/</span> Simple tasks. Softer days.</span>
    </footer>

    <!-- Native modal: confirmation before removing a task; Escape closes it. -->
    <dialog id="delete-dialog" aria-labelledby="delete-title" aria-describedby="delete-description">
      <h2 id="delete-title">Delete this task?</h2>
      <p id="delete-description"></p>

      <div class="dialog-actions">
        <button type="button" id="keep-task" class="secondary">Keep task</button>
        <button type="button" id="confirm-delete" class="primary">Delete task</button>
      </div>
    </dialog>
  </body>
</html>
