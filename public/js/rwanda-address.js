/**
 * Cascading Rwanda address selects: province > district > sector > cell > village.
 * Usage: RwandaAddress.attach(document.getElementById('myLocation'), { district, sector, cell, village })
 * The container holds <select data-level="province|district|sector|cell|village"> elements.
 * The tree is fetched once from the data-src URL and shared by every instance.
 */
(function () {
  var LEVELS = ['province', 'district', 'sector', 'cell', 'village'];
  var treePromise = null;

  function loadTree(url) {
    if (!treePromise) {
      treePromise = fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { if (!r.ok) { throw new Error('Could not load locations'); } return r.json(); })
        .catch(function (e) { treePromise = null; throw e; });
    }
    return treePromise;
  }

  function keys(node) { return node ? (Array.isArray(node) ? node : Object.keys(node)) : []; }

  function fill(select, names, placeholder, selected) {
    select.innerHTML = '';
    var first = document.createElement('option');
    first.value = '--';
    first.textContent = placeholder;
    select.appendChild(first);
    names.forEach(function (name) {
      var o = document.createElement('option');
      o.value = o.textContent = name;
      select.appendChild(o);
    });
    if (selected && names.indexOf(selected) === -1) {
      // Keep a legacy value that is not in the official list so it is not silently lost.
      var legacy = document.createElement('option');
      legacy.value = legacy.textContent = selected;
      select.appendChild(legacy);
    }
    select.value = selected || '--';
    select.disabled = names.length === 0 && !selected;
  }

  function attach(container, values) {
    values = values || {};
    var sel = {};
    LEVELS.forEach(function (l) { sel[l] = container.querySelector('[data-level="' + l + '"]'); });

    return loadTree(container.dataset.src).then(function (tree) {
      var label = function (l) { return sel[l].dataset.placeholder || '---Choose ' + l + '---'; };

      function provinceOf(district) {
        return keys(tree).filter(function (p) { return tree[p][district]; })[0];
      }
      function districtsOf(province) {
        if (province && province !== '--') { return keys(tree[province]); }
        var all = [];
        keys(tree).forEach(function (p) { all = all.concat(keys(tree[p])); });
        return all.sort(function (a, b) { return a.localeCompare(b); });
      }
      function node(province, district, sector, cell) {
        var p = province && province !== '--' ? province : provinceOf(district);
        var n = p && tree[p];
        // undefined = level not requested (stop here); empty/'--' = requested but not chosen (no children).
        [district, sector, cell].some(function (k) {
          if (k === undefined) { return true; }
          n = n && k && k !== '--' ? n[k] : null;
          return false;
        });
        return n;
      }
      function current(level) { var v = sel[level].value; return v === '--' ? '' : v; }

      function render(from, picked) {
        picked = picked || {};
        var idx = LEVELS.indexOf(from);
        if (idx <= 1) { fill(sel.district, districtsOf(current('province')), label('district'), picked.district); }
        if (idx <= 2) {
          var d = node(current('province'), current('district'));
          fill(sel.sector, keys(d), label('sector'), picked.sector);
        }
        if (idx <= 3) {
          var s = node(current('province'), current('district'), current('sector'));
          fill(sel.cell, keys(s), label('cell'), picked.cell);
        }
        if (idx <= 4) {
          var c = node(current('province'), current('district'), current('sector'), current('cell'));
          fill(sel.village, keys(c), label('village'), picked.village);
        }
      }

      fill(sel.province, keys(tree), label('province'), values.province || (values.district ? provinceOf(values.district) : ''));
      render('district', values);

      LEVELS.slice(0, 4).forEach(function (level, i) {
        sel[level].onchange = function () {
          if (level === 'district' && sel.province.value === '--') {
            var p = provinceOf(sel.district.value);
            if (p) { sel.province.value = p; }
          }
          render(LEVELS[i + 1]);
        };
      });
    });
  }

  window.RwandaAddress = { attach: attach };
})();
