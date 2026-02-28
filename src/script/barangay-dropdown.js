/**
 * Populate any <select> element with all Angeles City barangays.
 * @param {string} selectId - the id of the <select> element
 * @param {string|null} selectedValue - pre-selected barangay
 */
function populateBarangayDropdown(selectId, selectedValue = null) {
  const select = document.getElementById(selectId);
  if (!select) return;

  select.innerHTML = "";

  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.textContent = "-- Select Barangay --";
  select.appendChild(defaultOption);

  Object.keys(ANGELES_BARANGAYS)
    .sort()
    .forEach((name) => {
      const option = document.createElement("option");
      option.value = name;
      option.textContent = name.replace(/\b\w/g, (c) => c.toUpperCase());
      if (selectedValue && selectedValue.toLowerCase() === name) {
        option.selected = true;
      }
      select.appendChild(option);
    });
}
