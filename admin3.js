import { getTasks, addTask, updateTask, deleteTask, getSingleTask, getStats, getUsers } from './api.js';

document.addEventListener('DOMContentLoaded', async function() {

    // --- Dashboard Load Tasks ---
    if (document.getElementById('todayTasks')) {
        try {
            const today = new Date().toISOString().split('T')[0];
            const result = await getTasks(null, { start: today, end: today });
            if (result.success && result.data) {
                if (Array.isArray(result.data)) {
                    renderTasks(result.data, 'todayTasks');
                } else {
                    renderTasks([result.data], 'todayTasks');
                }
            } else {
                renderTasks([], 'todayTasks');
            }
        } catch (error) {
            console.error('Failed to load tasks:', error);
        }
    }

    if (document.getElementById('totalUsers')) {
        try {
            const stats = await getStats();
            document.getElementById('totalUsers').textContent = stats.totalUsers;
            document.getElementById('completedTasks').textContent = stats.completedTasks;
            document.getElementById('pendingTasks').textContent = stats.pendingTasks;
        } catch (error) {
            console.error('Failed to load admin stats:', error);
        }
    }

    // --- Load all tasks for Recent Activities on the Admin Dashboard ---
    if (document.getElementById('recentActivitiesBody')) {
        try {
            const result = await getTasks();
            if (result.success && Array.isArray(result.data)) {
                renderRecentActivities(result.data, 'recentActivitiesBody');
            } else {
                console.error("Failed to load recent activities or data is not an array.");
                renderRecentActivities([], 'recentActivitiesBody');
            }
        } catch (error) {
            console.error('Failed to load recent activities:', error);
        }
    }

    // --- Manage Users Page Functionality ---
    if (document.getElementById('usersTable')) {
        await renderUsers();
    }
});

// A robust version of loadRecords (already in your code)
async function loadRecords(recordType) {
    let dateRange = null;
    const today = new Date();
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    switch (recordType) {
        case 'daily':
            const todayStr = today.toISOString().split('T')[0];
            dateRange = { start: todayStr, end: todayStr };
            break;
        case 'weekly':
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            dateRange = {
                start: weekStart.toISOString().split('T')[0],
                end: weekEnd.toISOString().split('T')[0]
            };
            break;
        case 'monthly':
            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            dateRange = {
                start: monthStart.toISOString().split('T')[0],
                end: monthEnd.toISOString().split('T')[0]
            };
            break;
        case 'yearly':
            const yearStart = new Date(today.getFullYear(), 0, 1);
            const yearEnd = new Date(today.getFullYear(), 11, 31);
            dateRange = {
                start: yearStart.toISOString().split('T')[0],
                end: yearEnd.toISOString().split('T')[0]
            };
            break;
        case 'custom':
            if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
                dateRange = {
                    start: startDateInput.value,
                    end: endDateInput.value
                };
            } else {
                alert('Please select both start and end dates');
                return;
            }
            break;
    }

    try {
        const result = await getTasks(null, dateRange);
        if (result.success && result.data) {
            if (Array.isArray(result.data)) {
                renderTasks(result.data, 'recordsTable');
            } else {
                renderTasks([result.data], 'recordsTable');
            }
        } else {
            renderTasks([], 'recordsTable');
        }
    } catch (error) {
        console.error('Failed to load records:', error);
    }
}

// Function to fetch and render the list of users
async function renderUsers() {
    const usersTableBody = document.getElementById('usersTable');
    if (!usersTableBody) {
        console.error('usersTable element not found.');
        return;
    }
    
    usersTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading users...</td></tr>';

    try {
        const result = await getUsers();
        if (result.success && Array.isArray(result.data)) {
            usersTableBody.innerHTML = '';
            
            result.data.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.department}</td>
                    <td>${user.hod_name}</td>
                    <td>${user.role}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-user-btn" data-user-id="${user.id}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-user-btn" data-user-id="${user.id}">Delete</button>
                    </td>
                `;
                usersTableBody.appendChild(row);
            });
        } else {
            usersTableBody.innerHTML = '<tr><td colspan="6" class="text-center">No users found.</td></tr>';
        }
    } catch (error) {
        console.error('Failed to fetch users:', error);
        usersTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Failed to load users. Please try again.</td></tr>';
    }
}

// --- Add Task Form Handler ---
const taskForm = document.getElementById('taskForm');
if (taskForm) {
    taskForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const serialNo = document.getElementById('serialNo');
        const task_title = document.getElementById('task_title');
        const task_description = document.getElementById('task_description');
        const taskDate = document.getElementById('taskDate');
        const status = document.getElementById('status');

        if (!serialNo || !task_title || !task_description || !taskDate || !status) {
            console.error("One or more task form elements are missing from the HTML.");
            const taskMessage = document.getElementById('taskMessage');
            if (taskMessage) {
                taskMessage.textContent = 'Failed to add task: Form elements are missing.';
                taskMessage.style.color = 'red';
            }
            return;
        }

        const taskData = {
            serialNo: serialNo.value,
            task_title: task_title.value,
            task_description: task_description.value,
            task_date: taskDate.value,
            status: status.value
        };

        try {
            const result = await addTask(taskData);
            const taskMessage = document.getElementById('taskMessage');
            if (result.success) {
                taskMessage.textContent = 'Task added successfully!';
                taskMessage.style.color = 'green';
                taskForm.reset();
            } else {
                taskMessage.textContent = result.message;
                taskMessage.style.color = 'red';
            }
        } catch (error) {
            const taskMessage = document.getElementById('taskMessage');
            taskMessage.textContent = 'Failed to add task. Please try again.';
            taskMessage.style.color = 'red';
            console.error('Add Task Error:', error);
        }
    });
}

// --- Edit Task Modal Handlers ---
const editTaskModal = document.getElementById('editTaskModal');
if (editTaskModal) {
    editTaskModal.addEventListener('show.bs.modal', async function(event) {
        const button = event.relatedTarget;
        const taskId = button.getAttribute('data-task-id');
        
        if (!taskId) {
            console.error("Task ID is missing on the edit button.");
            return;
        }

        try {
            const taskResult = await getSingleTask(taskId);
            const taskData = taskResult.data;

            if (taskData) {
                document.getElementById('editTaskId').value = taskData.id;
                document.getElementById('editSerialNo').value = taskData.serialNo;
                document.getElementById('editActivity').value = taskData.task_title;
                document.getElementById('editDescription').value = taskData.task_description;
                document.getElementById('editTaskDate').value = taskData.task_date || '';
                document.getElementById('editStatus').value = taskData.status;
            } else {
                console.error("Could not fetch task data for ID:", taskId);
            }
        } catch (error) {
            console.error("Failed to fetch task data:", error);
        }
    });

    const saveEditBtn = document.getElementById('saveEditBtn');
    if (saveEditBtn) {
        saveEditBtn.addEventListener('click', async function() {
            const taskId = document.getElementById('editTaskId').value;
            if (!taskId) {
                console.error("Task ID is missing. Cannot save changes.");
                return;
            }

            const updatedTaskData = {
                serialNo: document.getElementById('editSerialNo').value,
                task_title: document.getElementById('editActivity').value,
                task_description: document.getElementById('editDescription').value,
                task_date: document.getElementById('editTaskDate').value,
                status: document.getElementById('editStatus').value
            };

            try {
                const result = await updateTask(taskId, updatedTaskData);
                
                if (result && result.success) {
                    const modalInstance = bootstrap.Modal.getInstance(editTaskModal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    location.reload(); 
                } else {
                    console.error('Task update failed:', result ? result.message : 'Unknown error');
                }
            } catch (error) {
                console.error('Error updating task:', error);
            }
        });
    }
}

// --- Delete task listener for dynamically created buttons ---
document.addEventListener('click', async function(event) {
    if (event.target.classList.contains('delete-task')) {
        if (confirm('Are you sure you want to delete this task?')) {
            const taskId = event.target.getAttribute('data-task-id');
            try {
                await deleteTask(taskId);
                event.target.closest('tr').remove();
            } catch (error) {
                console.error('Failed to delete task:', error);
            }
        }
    }
});

// --- Update status listener for dynamically created buttons ---
document.addEventListener('click', async function(event) {
    if (event.target.classList.contains('update-status')) {
        const taskId = event.target.getAttribute('data-task-id');
        const newStatus = event.target.getAttribute('data-status');
        try {
            await updateTask(taskId, { status: newStatus });
            location.reload(); 
        } catch (error) {
            console.error('Failed to update task status:', error);
        }
    }
});

function renderRecentActivities(tasks, elementId) {
    const tableBody = document.getElementById(elementId);
    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (!Array.isArray(tasks) || tasks.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="5" class="text-center">No recent activities found</td>`;
        tableBody.appendChild(row);
        return;
    }
    
    const sortedTasks = tasks.sort((a, b) => new Date(b.task_date) - new Date(a.task_date));
    const recentTasks = sortedTasks.slice(0, 5);
    
    recentTasks.forEach(task => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${task.username}</td> 
            <td>${task.serialNo}</td>
            <td>${task.task_title}</td>
            <td>${task.task_date}</td>
            <td>
                <span class="status-badge status-${task.status}">
                    ${task.status === 'completed' ? 'Completed' : 'Pending'}
                </span>
            </td>
        `;
        tableBody.appendChild(row);
    });
}