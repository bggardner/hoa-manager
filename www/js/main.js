class HOA {

  static addEventListeners(parentElement) {

    // File uploader with spinner control
    parentElement.querySelectorAll('.form-floating input[type="file"][data-upload]').forEach(element => {
      element.addEventListener('change', event => {
        if (element.files.length != 1) {
          return;
        }
        const data = new FormData();
        data.append('file', element.files[0]);
        element.nextElementSibling.classList.remove('d-none'); // Show spinner
        HOA.fetch('method=upload', {
          method: 'POST',
          body: data
        }).then(data => {
          element.nextElementSibling.classList.add('d-none'); // Hide spinner
          if (element.dataset.upload == "multiple") {
            const newFileDiv = HOA.template(`<div class="input-group my-1">
              <a class="form-control text-start link-primary" href="${window['web_root']}/getUpload?hash=${data.hash}&name=${element.files[0].name}">${element.files[0].name}</a>
              <input type="hidden" name="${element.dataset.nameField || 'file_names[]'}" value="${element.files[0].name}">
              <input type="hidden" name="${element.dataset.target || 'files[]'}" value="${data.hash}">
              <div class="btn btn-danger" data-target=".input-group" data-role="remove"><i class="bi-x-lg"></i></div>
            </div>`);
            HOA.addEventListeners(newFileDiv);
            element.closest('.form-floating').querySelector('[data-container="file"]').append(newFileDiv);
            element.value = "";
          }
        }).catch(error => {
          element.nextElementSibling.classList.add('d-none'); // Hide spinner
        });
      });
    });

    parentElement.querySelectorAll('.form-floating [data-container] [data-role="add"]').forEach(element => {
      element.addEventListener('click', event => {
        const inputGroup = element.closest('.input-group').cloneNode(true);
        inputGroup.querySelector('[data-role="remove"]').classList.remove('d-none');
        inputGroup.querySelector('[data-role="add"]').remove();
        HOA.addEventListeners(inputGroup);
        element.closest('[data-container]').insertBefore(inputGroup, element.closest('.input-group'));
        element.closest('.input-group').querySelectorAll('input').forEach(element => {
          element.value = '';
        });
      });
    });

    parentElement.querySelectorAll('[data-autocomplete]').forEach(this.autocomplete);

    parentElement.querySelectorAll('[data-target][data-role="remove"]').forEach(element => {
      element.addEventListener('click', event => {
          element.classList.add('d-none');
          const target = element.closest(element.dataset.target);
          target.querySelector('[data-role="undo"]').classList.remove('d-none');
          target.querySelectorAll('input').forEach(element => {
            element.disabled = true;
            element.classList.add('text-decoration-line-through');
          });
//        element.closest(element.dataset.target).remove();
      });
    });

    parentElement.querySelectorAll('[data-target][data-role="undo"]').forEach(element => {
      element.addEventListener('click', event => {
          element.classList.add('d-none');
          const target = element.closest(element.dataset.target);
          target.querySelector('[data-role="remove"]').classList.remove('d-none');
          target.querySelectorAll('input').forEach(element => {
            element.disabled = false;
            element.classList.remove('text-decoration-line-through');
          });
      });
    });

  }

  static autocomplete(element) {
    const cache = {};
    element.autocomplete = 'off';
    element.parentNode.classList.add('dropdown');
    element.setAttribute('data-bs-toggle', 'dropdown');
    element.classList.add('dropdown-toggle');
    const dropdownElement = HOA.template('<div class="dropdown-menu"></div>');
    element.after(dropdownElement);
    const dropdown = new bootstrap.Dropdown(element);
    let hideTimeout;
    element.addEventListener('blur', event => {
      hideTimeout = setTimeout(() => {
        dropdown.hide();
      }, 100);
    });
    element.addEventListener('hide.bs.dropdown', event => {
      if (element == document.activeElement) {
        event.preventDefault();
      }
    });
    element.addEventListener('focus', event => {
      clearTimeout(hideTimeout);
      element.dispatchEvent(new InputEvent('input'));
    });
    element.addEventListener('input', event => {
      if (element.value in cache) {
        if (cache[element.value].length) {
          dropdownElement.innerHTML = '';
          cache[element.value].forEach(child => {
            dropdownElement.append(child);
          });
        } else {
          dropdownElement.innerHTML = '<button type="button" class="dropdown-item text-secondary fst-italic" disabled>No results</button>';
        }
        dropdown.show(); // Causes hide()
        return;
      }
      dropdownElement.innerHTML = '<button type="button" class="dropdown-item text-secondary fst-italic" disabled>Loading...</button>';
      HOA.fetch(`method=autocomplete&field=${element.dataset['autocomplete']}&term=${encodeURIComponent(element.value)}`).then(data => {
        cache[element.value] = [];
        const results = data.results;
        if (results.length == 0) {
          dropdownElement.innerHTML = '<button type="button" class="dropdown-item text-secondary fst-italic" disabled>No results</button>';
          dropdown.show();
          return;
        }
        dropdownElement.innerHTML = '';
        results.forEach(result => {
          const item = {
            label: result.label ?? (result.value ?? result),
            value: result.value ?? result
          };
          const index = item.label.search(new RegExp('\\b' + element.value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i'));
          const label = item.label.substring(0, index)
            + `<span class="fw-bold">${item.label.substring(index, index + element.value.length)}</span>`
            + item.label.substring(index + element.value.length);
          const dropdownItem = HOA.template(`<button type="button" class="dropdown-item" data-value="${item.value}">${label}</button>`);
          dropdownItem.addEventListener('click', event => {
            element.value = HOA.template(`<p>${item.label}</p>`).innerText;
            if (element.dataset.hasOwnProperty('target')) {
              const target = element.closest('form').querySelector(`#${element.dataset.target}`);
              target.value = item.value;
              target.dispatchEvent(new Event('change'));
            }
            dropdownElement.innerHTML = '';
            dropdown.hide();
            //element.focus();
          });
          dropdownItem.addEventListener('blur', event => {
            hideTimeout = setTimeout(() => {
              dropdown.hide();
            }, 100);
          });
          dropdownItem.addEventListener('focus', event => {
            clearTimeout(hideTimeout);
          });
          dropdownElement.appendChild(dropdownItem);
          cache[element.value].push(dropdownItem);;
        });
        dropdown.show();
      });
    });
  }

  static error(error) {
      HOA.modal(HOA.template(`<div class="modal-content">
      <div class="modal-header alert-danger">
        <h5 class="modal-title">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ${error}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>`));
      return Promise.reject(error);
  }

  static fetch(queryString, init = {}) {
    return fetch(`${window['web_root']}/ajax.php?${queryString}`, init).then(response => {
      // Handle bad responses
      if (response.ok) {
        if (response.headers.get('content-type').startsWith('text/')) {
          return response.text();
        }
        return response;
      }
      throw `${response.status}: ${response.statusText}`;
    }).then(data => {
      if (data instanceof Response) {
        return data;
      }
      // response.json() errors aren't caught with catch(), so parse with JSON.parse()
      try {
        data = JSON.parse(data);
      } catch (error) {
        throw data;
      }
      if (data.hasOwnProperty('error')) {
        throw data.error;
      }
      return data;
    }).catch(HOA.error);
  }

  static modal(content) {
      // TODO: Bootstrap can't handle multiple modals/offcanvas, so just remove them for now
      document.querySelectorAll('.modal-backdrop, .modal, .offcanvas').forEach(element => {
        element.remove();
      });
      const modalElement = HOA.template(`<div class="modal fade" data-bs-backdrop="static" aria-labelledby="#modal-title">
  <div class="modal-dialog">
  </div>
</div>`);
      modalElement.querySelector('.modal-dialog').append(content);
      document.querySelector('body').append(modalElement);
      const modal = new bootstrap.Modal(modalElement);
      modalElement.addEventListener('hidden.bs.modal', event => {
        modalElement.remove();
      });
      modal.show();
      return modalElement;
  }

  static template(html) {
    const template = document.createElement('template');
    template.innerHTML = html;
    this.addEventListeners(template.content);
    return template.content.firstChild;
  }

}

document.addEventListener('DOMContentLoaded', event => {

  HOA.addEventListeners(document);

  document.querySelectorAll('[data-role="county-data"]').forEach(element => {
    element.addEventListener('click', event => {
      element.disabled = true;
      document.querySelectorAll('#parcels tbody tr[data-id] td:last-child').forEach(cell => {
        cell.innerHTML = '<span class="text-secondary">Loading...</span>';
      });
      HOA.fetch(`method=myplace`).then(data => {
        data.results.forEach(parcel => {
          const row = document.querySelector(`#parcels tbody tr[data-id="${parcel.PARCEL_ID}"]`);
          if (!row) {
            return;
          }
          row.querySelector('td:last-child').innerHTML = `<div class="d-flex gap-3 align-items-center"><span>${parcel.DEEDED_OWNER}</span></div>`;
          if (row.querySelector('td:nth-last-child(2)').innerText != row.querySelector('td:last-child span').innerText) {
            row.querySelector('td:last-child div').insertAdjacentHTML('afterbegin', `<a class="btn btn-sm btn-primary" title="Update" href="?id=${parcel.PARCEL_ID}&owner=${encodeURIComponent(parcel.DEEDED_OWNER)}"><i class="bi-arrow-left"></i></a>`);
          }
        });
        element.disabled = false;
      });
    });
  });

  document.querySelectorAll('#addCategoryForm select[name="parent"]').forEach(element => {
    const editBtn = element.parentElement.querySelector('[data-role="edit"]');
    editBtn.addEventListener('click', event => {
      const selectedOption = element.selectedOptions[0];
      const form = document.querySelector('#editCategoryForm');
      form.querySelector('input[name="id"]').value = selectedOption.value;
      form.querySelectorAll('select[name="parent"] option').forEach(option => {
        if (parseInt(option.dataset.left) < parseInt(selectedOption.dataset.left) && parseInt(option.dataset.right) > parseInt(selectedOption.dataset.right)) {
          option.selected = true; // Assumes hierarchical sort
        }
      });
      form.querySelector('input[name="name"]').value = selectedOption.dataset.name;
      form.classList.remove('d-none');
    });
    element.addEventListener('change', event => {
      if (element.selectedOptions[0].value == 1) {
        editBtn.classList.add('d-none');
      } else {
        editBtn.classList.remove('d-none');
      }
    });
  });

  document.querySelectorAll('#editCategoryForm [data-role="cancel"]').forEach(element => {
    element.addEventListener('click', event => {
      element.closest('form').classList.add('d-none');
    });
  });

  document.querySelectorAll('.editable-rows tr [data-role="edit"]').forEach(element => {
    element.addEventListener('click', event => {
      const row = element.closest('tr');
      row.querySelectorAll(':disabled').forEach(element => {
        element.classList.add('was-disabled');
        element.disabled = false;
      });
      row.querySelectorAll('.d-none').forEach(element => {
        element.classList.add('was-hidden');
        element.classList.remove('d-none');
      });
      row.querySelectorAll('span').forEach(element => {
        element.classList.add('d-none');
      });
      element.classList.add('d-none');
    });
  });

  document.querySelectorAll('.editable-rows tr [data-role="cancel"]').forEach(element => {
    element.addEventListener('click', event => {
      const row = element.closest('tr');
      row.querySelectorAll('.was-disabled').forEach(element => {
        element.disabled = true;
      });
      row.querySelectorAll('.was-hidden').forEach(element => {
        element.classList.add('d-none');
      });
      row.querySelectorAll('span').forEach(element => {
        element.classList.remove('d-none');
      });
      row.querySelector('[data-role="edit"]').classList.remove('d-none');
    });
  });

  document.querySelectorAll('[data-role="delete"]').forEach(element => {
    element.addEventListener('click', function(event) {
      if (!confirm('Are you sure? This cannot be undone. Click OK to confirm.')) {
        event.preventDefault();
        event.stopPropagation();
        return false;
      }
    }, false)
  });

  document.querySelectorAll('.add-menu.dropdown-menu button[data-target]').forEach(element => {
    element.addEventListener('click', event => {
      element.closest('form').classList.add('d-none');
      document.querySelector(`#${element.dataset.target}`).classList.remove('d-none');
    });
  });

  document.querySelectorAll('input[data-check="all"]').forEach(element => {
    element.addEventListener('change', event => {
      element.closest('form').querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = element.checked;
      });
    });
  });

  document.querySelectorAll('form [type="submit"][name="batch"][value="statements"]').forEach(element => {
    element.addEventListener('click', event => {
      const form = element.closest('form');
      const modalContent = HOA.template(`<div class="modal-content">
  <div class="modal-header">
    <div class="modal-title">Statement Options</div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  </div>
  <div class="modal-body">
    <div class="form-floating mb-3">
      <div class="input-group">
        <input type="date" name="start" id="start" value="${form.querySelector('[name="start"]').value}" class="form-input">
        <div class="input-group-text">to</div>
        <input type="date" name="end" value="${form.querySelector('[name="end"]').value}" class="form-input">
      </div>
      <label for="label">Date Range</label>
    </div>
    <div class="form-floating mb-3">
      <input class="form-control" type="number" min="0" step="1" id="offset" value="${form.querySelector('[name="offset"]').value}">
      <label for="offset">Offset</label>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary">Generate</button>
  </div>
</div>`);
      HOA.fetch('method=labels').then(data => {
        const labels = data.results;
        labels.forEach(label => {
          const option = HOA.template(`<option value="${label.id}"${label.id == element.dataset.label ? ' selected' : ''}>${label.name}</option>`);
          modalContent.querySelector('#label').append(option);
        });
        const modalElement = HOA.modal(modalContent);
        modalElement.querySelector('.btn-primary').addEventListener('click', event => {
          const label = form.querySelector('[name="label"]');
          label.value = modalElement.querySelector('#label').selectedOptions[0].value;
          label.disabled = false;
          const offset = form.querySelector('[name="offset"]');
          offset.value = modalElement.querySelector('#offset').value;
          offset.disabled = false;

          // Simulate submit-by-button-click
          form.querySelectorAll('[name="batch"]').forEach(button => {
            button.disabled = true;
          });
          form.append(HOA.template(`<input type="hidden" name="batch" value="${element.value}">`));
          form.target = element.formtarget;
          form.submit();
          form.querySelector('input[type="hidden"][name="batch"]').remove();
          form.querySelectorAll('[name="batch"]').forEach(button => {
            button.disabled = false;
          });
          bootstrap.Modal.getInstance(modalElement).hide();
        });
      });
      event.preventDefault();
      event.stopPropagation();
      return false;
    });
  });

  document.querySelectorAll('form [type="submit"][name="batch"][data-label]').forEach(element => {
    element.addEventListener('click', event => {
      const form = element.closest('form');
      const modalContent = HOA.template(`<div class="modal-content">
  <div class="modal-header">
    <div class="modal-title">Label Options</div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  </div>
  <div class="modal-body">
    <div class="form-floating mb-3">
      <select class="form-select" name="label" id="label">
      </select>
      <label for="label">Label</label>
    </div>
    <div class="form-floating mb-3">
      <input class="form-control" type="number" min="0" step="1" id="offset" value="${form.querySelector('[name="offset"]').value}">
      <label for="offset">Offset</label>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary">Generate</button>
  </div>
</div>`);
      HOA.fetch('method=labels').then(data => {
        const labels = data.results;
        labels.forEach(label => {
          const option = HOA.template(`<option value="${label.id}"${label.id == element.dataset.label ? ' selected' : ''}>${label.name}</option>`);
          modalContent.querySelector('#label').append(option);
        });
        const modalElement = HOA.modal(modalContent);
        modalElement.querySelector('.btn-primary').addEventListener('click', event => {
          const label = form.querySelector('[name="label"]');
          label.value = modalElement.querySelector('#label').selectedOptions[0].value;
          label.disabled = false;
          const offset = form.querySelector('[name="offset"]');
          offset.value = modalElement.querySelector('#offset').value;
          offset.disabled = false;

          // Simulate submit-by-button-click
          form.querySelectorAll('[name="batch"]').forEach(button => {
            button.disabled = true;
          });
          form.append(HOA.template(`<input type="hidden" name="batch" value="${element.value}">`));
          form.target = element.formtarget;
          form.submit();
          form.querySelector('input[type="hidden"][name="batch"]').remove();
          form.querySelectorAll('[name="batch"]').forEach(button => {
            button.disabled = false;
          });
          bootstrap.Modal.getInstance(modalElement).hide();
        });
      });
      event.preventDefault();
      event.stopPropagation();
      return false;
    });
  });

  document.querySelectorAll('form#profile').forEach(element => {
    element.addEventListener('submit', event => {
      element.querySelectorAll('#phone .input-group').forEach(inputGroup => {
        if (inputGroup.querySelector('[name="data[phone][values][]"]').value != "") {
          if (inputGroup.querySelector('[name="data[phone][keys][]"]').value == "") {
            alert('A label is required for each phone number');
            event.preventDefault();
            event.stopPropagation();
            return false;
          }
        }
      });
    });
  });
});
