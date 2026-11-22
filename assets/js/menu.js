/* Public digital menu — cart + ordering. No dependencies, no innerHTML with
   untrusted strings: every dynamic text node is written with textContent. */
(function () {
    'use strict';

    var app = document.getElementById('menu-main');
    if (!app) {
        return;
    }

    var api = app.getAttribute('data-api') || '';
    var csrf = app.getAttribute('data-csrf') || '';
    var tenant = app.getAttribute('data-tenant') || '';
    var menuToken = app.getAttribute('data-menu-token') || '';
    var tableToken = app.getAttribute('data-table-token') || '';

    var storageKey = 'monochi_cart_' + (menuToken || tenant || 'default');
    var MAX_QTY = 20;

    var cartBar = document.getElementById('cart-bar');
    var cartCount = document.getElementById('cart-count');
    var cartTotal = document.getElementById('cart-total');
    var cartOpen = document.getElementById('cart-open');
    var cartDialog = document.getElementById('cart-dialog');
    var cartList = document.getElementById('cart-list');
    var cartEmpty = document.getElementById('cart-empty');
    var cartSum = document.getElementById('cart-sum');
    var cartNote = document.getElementById('cart-note');
    var cartPhone = document.getElementById('cart-phone');
    var cartName = document.getElementById('cart-name');
    var cartError = document.getElementById('cart-error');
    var cartSubmit = document.getElementById('cart-submit');
    var doneDialog = document.getElementById('done-dialog');
    var doneCode = document.getElementById('done-code');
    var toast = document.getElementById('toast');
    var rowTemplate = document.getElementById('cart-row-template');

    var numberFormatter;
    try {
        numberFormatter = new Intl.NumberFormat('fa-IR');
    } catch (e) {
        numberFormatter = null;
    }

    var cart = loadCart();
    var submitting = false;

    function money(value) {
        var n = Math.max(0, Math.round(Number(value) || 0));
        var text = numberFormatter ? numberFormatter.format(n) : String(n);
        return text.replace(/\u066C/g, '٬') + ' تومان';
    }

    function loadCart() {
        try {
            var raw = window.localStorage.getItem(storageKey);
            if (!raw) {
                return {};
            }
            var parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') {
                return {};
            }
            var clean = {};
            Object.keys(parsed).forEach(function (key) {
                var item = parsed[key];
                if (!item || typeof item !== 'object') {
                    return;
                }
                var id = parseInt(item.id, 10);
                var price = parseInt(item.price, 10);
                var qty = parseInt(item.qty, 10);
                if (!id || !price || !qty || qty < 1) {
                    return;
                }
                clean[id] = {
                    id: id,
                    name: String(item.name || ''),
                    price: price,
                    qty: Math.min(qty, MAX_QTY),
                    image: String(item.image || '')
                };
            });
            return clean;
        } catch (e) {
            return {};
        }
    }

    function saveCart() {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(cart));
        } catch (e) {
            /* private mode: the cart still works for this page view */
        }
    }

    function items() {
        return Object.keys(cart).map(function (key) {
            return cart[key];
        });
    }

    function totals() {
        var qty = 0;
        var sum = 0;
        items().forEach(function (item) {
            qty += item.qty;
            sum += item.qty * item.price;
        });
        return { qty: qty, sum: sum };
    }

    function updateBadge() {
        var t = totals();
        if (cartCount) {
            cartCount.textContent = numberFormatter ? numberFormatter.format(t.qty) : String(t.qty);
        }
        if (cartTotal) {
            cartTotal.textContent = money(t.sum);
        }
        if (cartSum) {
            cartSum.textContent = money(t.sum);
        }
        if (cartBar) {
            cartBar.hidden = t.qty === 0;
        }
    }

    function showToast(message) {
        if (!toast) {
            return;
        }
        var line = toast.firstElementChild;
        if (!line) {
            line = document.createElement('span');
            toast.appendChild(line);
        }
        line.textContent = message;
        toast.hidden = false;
        window.clearTimeout(showToast._timer);
        showToast._timer = window.setTimeout(function () {
            toast.hidden = true;
        }, 2600);
    }

    function showError(message) {
        if (!cartError) {
            return;
        }
        cartError.textContent = message;
        cartError.hidden = false;
    }

    function clearError() {
        if (cartError) {
            cartError.hidden = true;
            cartError.textContent = '';
        }
    }

    function addFromCard(card) {
        var id = parseInt(card.getAttribute('data-id'), 10);
        var price = parseInt(card.getAttribute('data-price'), 10);
        var name = card.getAttribute('data-name') || '';
        if (!id || !price) {
            return;
        }

        if (cart[id]) {
            cart[id].qty = Math.min(cart[id].qty + 1, MAX_QTY);
        } else {
            cart[id] = {
                id: id,
                name: name,
                price: price,
                qty: 1,
                image: card.getAttribute('data-image') || ''
            };
        }

        saveCart();
        updateBadge();
        showToast('«' + name + '» به سبد اضافه شد.');
    }

    function changeQty(id, delta) {
        if (!cart[id]) {
            return;
        }
        cart[id].qty += delta;
        if (cart[id].qty <= 0) {
            delete cart[id];
        } else if (cart[id].qty > MAX_QTY) {
            cart[id].qty = MAX_QTY;
        }
        saveCart();
        renderCart();
        updateBadge();
    }

    function removeItem(id) {
        if (cart[id]) {
            delete cart[id];
            saveCart();
            renderCart();
            updateBadge();
        }
    }

    function renderCart() {
        if (!cartList || !rowTemplate) {
            return;
        }

        cartList.textContent = '';
        var list = items();

        if (cartEmpty) {
            cartEmpty.hidden = list.length > 0;
        }
        if (cartSubmit) {
            cartSubmit.disabled = list.length === 0;
        }

        list.forEach(function (item) {
            var node = rowTemplate.content.firstElementChild.cloneNode(true);
            node.setAttribute('data-id', String(item.id));

            var nameEl = node.querySelector('.cart-row__name');
            var priceEl = node.querySelector('.cart-row__price');
            var qtyEl = node.querySelector('[data-qty]');

            if (nameEl) {
                nameEl.textContent = item.name;
            }
            if (priceEl) {
                priceEl.textContent = money(item.price * item.qty);
            }
            if (qtyEl) {
                qtyEl.textContent = numberFormatter ? numberFormatter.format(item.qty) : String(item.qty);
            }

            node.querySelectorAll('[data-step]').forEach(function (button) {
                button.addEventListener('click', function () {
                    changeQty(item.id, parseInt(button.getAttribute('data-step'), 10) || 0);
                });
            });

            var remove = node.querySelector('.cart-row__remove');
            if (remove) {
                remove.addEventListener('click', function () {
                    removeItem(item.id);
                });
            }

            cartList.appendChild(node);
        });
    }

    function openCart() {
        if (!cartDialog) {
            return;
        }
        clearError();
        renderCart();
        cartDialog.showModal();
    }

    function currentType() {
        var checked = document.querySelector('input[name="order-type"]:checked');
        return checked ? checked.value : 'dine_in';
    }

    function submitOrder() {
        if (submitting) {
            return;
        }

        var list = items();
        if (list.length === 0) {
            showError('سبد خرید خالی است.');
            return;
        }

        clearError();
        submitting = true;
        if (cartSubmit) {
            cartSubmit.disabled = true;
            cartSubmit.textContent = 'در حال ثبت…';
        }

        var payload = {
            tenant: tenant,
            menu_token: menuToken,
            table_token: tableToken,
            type: currentType(),
            note: cartNote ? cartNote.value : '',
            customer_phone: cartPhone ? cartPhone.value : '',
            customer_name: cartName ? cartName.value : '',
            items: list.map(function (item) {
                return { id: item.id, qty: item.qty };
            })
        };

        window.fetch(api + '/orders', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(function (response) {
            return response.json().catch(function () {
                return { ok: false, error: 'پاسخ سرور قابل خواندن نبود.' };
            }).then(function (data) {
                return { status: response.status, data: data };
            });
        }).then(function (result) {
            if (!result.data || result.data.ok !== true) {
                var message = (result.data && result.data.error) || 'ثبت سفارش انجام نشد.';
                if (result.status === 419) {
                    message = 'صفحه منقضی شده است. آن را دوباره بارگذاری کن.';
                }
                showError(message);
                return;
            }

            cart = {};
            saveCart();
            updateBadge();
            renderCart();

            if (cartDialog && cartDialog.open) {
                cartDialog.close();
            }

            var code = result.data.order && result.data.order.code ? result.data.order.code : '—';
            if (doneCode) {
                doneCode.textContent = code;
            }
            if (doneDialog) {
                doneDialog.showModal();
            }
        }).catch(function () {
            showError('ارتباط با سرور برقرار نشد. اتصال اینترنت را بررسی کن.');
        }).then(function () {
            submitting = false;
            if (cartSubmit) {
                cartSubmit.disabled = items().length === 0;
                cartSubmit.textContent = 'ثبت سفارش';
            }
        });
    }

    /* ---- wiring ---------------------------------------------------------- */
    app.addEventListener('click', function (event) {
        var add = event.target.closest('[data-add]');
        if (add) {
            var card = add.closest('.product-card');
            if (card) {
                addFromCard(card);
            }
        }
    });

    if (cartOpen) {
        cartOpen.addEventListener('click', openCart);
    }
    if (cartSubmit) {
        cartSubmit.addEventListener('click', submitOrder);
    }

    /* Keep the note/text fields alive while the dialog is open. */
    document.querySelectorAll('.menu-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.menu-tab').forEach(function (other) {
                other.classList.toggle('is-active', other === tab);
            });
        });
    });

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }
                var id = entry.target.getAttribute('data-section');
                document.querySelectorAll('.menu-tab').forEach(function (tab) {
                    tab.classList.toggle('is-active', tab.getAttribute('data-tab') === id);
                });
            });
        }, { rootMargin: '-140px 0px -55% 0px', threshold: 0 });

        document.querySelectorAll('.menu-section').forEach(function (section) {
            observer.observe(section);
        });
    }

    updateBadge();
    renderCart();
}());
