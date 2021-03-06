module.exports = class extends Executor {

    constructor() {
        super();
        this.collapsed = false;
    }

    static __name() {

        return "nestable";
    }

    __invoke() {

        let result = $(this.target).nestable({
            maxDepth: this.target.dataset.maxDepth ? this.target.dataset.maxDepth : 15
        }).on('change', (e) => {
            let list = $(e.target);

            jax.lte_admin.nestable_save(
                e.target.dataset.model,
                e.target.dataset.maxDepth,
                list.nestable('serialize'),
                e.target.dataset.parent,
                e.target.dataset.orderField,
            )
        });

        if (this.collapsed) {
            $(this.target).nestable('collapseAll');
        }

        return result;
    }

    expand() {

        this.collapsed = false;
        return $(this.target.dataset.target ? this.target.dataset.target : '.dd').nestable('expandAll');
    }

    collapse() {

        this.collapsed = true;
        return $(this.target.dataset.target ? this.target.dataset.target : '.dd').nestable('collapseAll');
    }
}
