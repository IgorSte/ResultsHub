function save_new_order(type, ids_list)
{
    fetch(`api/order/` + type,
    {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ids_list: ids_list
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('The new order saved')
        } else {
            showError(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Connection error');
    })
    .finally(() => {
        document.getElementById('run-details-loader').style.display = 'none';
        document.getElementById('run-details-content').style.display = 'block';
    });
}

// moving sections
const tablesContainer = document.getElementById('tables-container');
if (tablesContainer !== null) {
    Sortable.create(tablesContainer, {
        animation: 150,
        handle: '.table-wrapper .section-grip',
        ghostClass: 'sortable-ghost',
        onEnd: function (evt) {
            const parentFromItem = evt.item.parentElement;
            var new_order_ids = [];

            for (const row of parentFromItem.children) {
                new_order_ids.push(row.getAttribute('data-section-id'))
            }

            save_new_order('sections', new_order_ids);
        }
    });
}

// moving run
document.querySelectorAll('.sortable-rows').forEach(tbody => {
    Sortable.create(tbody, {
        animation: 150,
        handle: '.run-grip',
        ghostClass: 'sortable-ghost',
        onEnd: function (evt) {
            const parentFromItem = evt.item.parentElement;
            var new_order_ids = [];

            for (const row of parentFromItem.children) {
                var run_id = row.getAttribute('data-run-id');
                new_order_ids.push(run_id)

                if (document.getElementById('run_progress_tr_' + run_id)) {
                    var progress_row = document.getElementById('run_progress_tr_' + run_id);
                    row.after(progress_row);
                }
            }

            save_new_order('runs', new_order_ids);
        }
    });
});