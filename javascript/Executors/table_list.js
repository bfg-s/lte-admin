'use strict';

class TableList extends Executor {

    checkChildCheckboxes () {
        let obj = this.storage.object;
        $(`[name^="${obj.name}"], .global_${obj.name}`).each((i,o) => {
            o.checked = true;
            $(o).trigger('change');
        });
    }

    uncheckChildCheckboxes () {
        let obj = this.storage.object;
        $(`[name^="${obj.name}"], .global_${obj.name}`).each((i,o) => {
            o.checked = false;
            $(o).trigger('change');
        });
    }

    invertChildCheckboxes () {
        let obj = this.storage.object;
        $(`[name^="${obj.name}"], .global_${obj.name}`).each((i,o) => {
            o.checked = !o.checked;
            if (o.name !== obj.name) {
                $(o).trigger('change');
            }
        });
    }

    invertByCheckChildCheckboxes () {
        let obj = this.storage.object;
        $(`[name^="${obj.name}"], .global_${obj.name}`).each((i,o) => {
            if (o.name !== obj.name) {
                o.checked = !o.checked;
                $(o).trigger('change');
            }
        });
    }

    primaryChange () {
        let obj = this.storage.object;
        let has = false;
        $(`.select_${obj.dataset.table}`).each((i,o) => {
            if (this.storage.object !== o && o.checked) {
                has = o.checked;
            }
        });
        if (!has && !this.storage.object.checked) {
            $(`.global_select_${obj.dataset.table}`).each((i, o) => {
                o.checked = false;
            });
        }
    }

    static __name () {

        return "table_list";
    }
}

module.exports = TableList;
