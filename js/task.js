function searchTask() {
    const query = document.getElementById('search').value.toLowerCase();
    const tasks = document.querySelectorAll('.task-item');

    tasks.forEach(task => {
        const taskName = task.querySelector('.task-name').textContent.toLowerCase();
        if (taskName.includes(query)) {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
} 