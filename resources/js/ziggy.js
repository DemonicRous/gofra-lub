const Ziggy = {
    "url": "http:\/\/localhost", "port": null, "defaults": {}, "routes": {
        "sanctum.csrf-cookie": {"uri": "sanctum\/csrf-cookie", "methods": ["GET", "HEAD"]},
        "pages.home": {"uri": "\/", "methods": ["GET", "HEAD"]},
        "login": {"uri": "login", "methods": ["GET", "HEAD"]},
        "register": {"uri": "register", "methods": ["GET", "HEAD"]},
        "password.request": {"uri": "forgot-password", "methods": ["GET", "HEAD"]},
        "password.email": {"uri": "forgot-password", "methods": ["POST"]},
        "password.reset": {"uri": "reset-password\/{token}", "methods": ["GET", "HEAD"], "parameters": ["token"]},
        "password.update": {"uri": "reset-password", "methods": ["POST"]},
        "logout": {"uri": "logout", "methods": ["POST"]},
        "verification.notice": {"uri": "email\/verify", "methods": ["GET", "HEAD"]},
        "verification.verify": {
            "uri": "email\/verify\/{id}\/{hash}",
            "methods": ["GET", "HEAD"],
            "parameters": ["id", "hash"]
        },
        "verification.send": {"uri": "email\/verification-notification", "methods": ["POST"]},
        "dashboard": {"uri": "dashboard", "methods": ["GET", "HEAD"]},
        "profile.edit": {"uri": "profile", "methods": ["GET", "HEAD"]},
        "profile.update": {"uri": "profile", "methods": ["PATCH"]},
        "profile.destroy": {"uri": "profile", "methods": ["DELETE"]},
        "tasks.index": {"uri": "tasks", "methods": ["GET", "HEAD"]},
        "tasks.store": {"uri": "tasks", "methods": ["POST"]},
        "tasks.show": {
            "uri": "tasks\/{task}",
            "methods": ["GET", "HEAD"],
            "parameters": ["task"],
            "bindings": {"task": "id"}
        },
        "tasks.update": {
            "uri": "tasks\/{task}",
            "methods": ["PUT"],
            "parameters": ["task"],
            "bindings": {"task": "id"}
        },
        "tasks.destroy": {
            "uri": "tasks\/{task}",
            "methods": ["DELETE"],
            "parameters": ["task"],
            "bindings": {"task": "id"}
        },
        "tasks.export.excel": {"uri": "tasks\/export\/excel", "methods": ["GET", "HEAD"]},
        "tasks.export.pdf": {"uri": "tasks\/export\/pdf", "methods": ["GET", "HEAD"]},
        "tasks.comments.store": {
            "uri": "tasks\/{task}\/comments",
            "methods": ["POST"],
            "parameters": ["task"],
            "bindings": {"task": "id"}
        },
        "tasks.subtasks.store": {
            "uri": "tasks\/{task}\/subtasks",
            "methods": ["POST"],
            "parameters": ["task"],
            "bindings": {"task": "id"}
        },
        "tasks.subtasks.toggle": {
            "uri": "tasks\/subtasks\/{subtask}",
            "methods": ["PATCH"],
            "parameters": ["subtask"],
            "bindings": {"subtask": "id"}
        },
        "tasks.bulk-update": {"uri": "tasks\/bulk-update", "methods": ["POST"]},
        "audits.index": {"uri": "audits", "methods": ["GET", "HEAD"]},
        "audits.create": {"uri": "audits\/create", "methods": ["GET", "HEAD"]},
        "audits.store": {"uri": "audits", "methods": ["POST"]},
        "audits.show": {
            "uri": "audits\/{audit}",
            "methods": ["GET", "HEAD"],
            "parameters": ["audit"],
            "bindings": {"audit": "id"}
        },
        "audits.update": {
            "uri": "audits\/{audit}",
            "methods": ["PUT"],
            "parameters": ["audit"],
            "bindings": {"audit": "id"}
        },
        "audits.destroy": {
            "uri": "audits\/{audit}",
            "methods": ["DELETE"],
            "parameters": ["audit"],
            "bindings": {"audit": "id"}
        },
        "audits.start": {
            "uri": "audits\/{audit}\/start",
            "methods": ["POST"],
            "parameters": ["audit"],
            "bindings": {"audit": "id"}
        },
        "audits.complete": {
            "uri": "audits\/{audit}\/complete",
            "methods": ["POST"],
            "parameters": ["audit"],
            "bindings": {"audit": "id"}
        },
        "audits.media.upload": {
            "uri": "audits\/{audit}\/media",
            "methods": ["POST"],
            "parameters": ["audit"],
            "bindings": {"audit": "id"}
        },
        "audits.media.delete": {
            "uri": "audits\/media\/{media}",
            "methods": ["DELETE"],
            "parameters": ["media"],
            "bindings": {"media": "id"}
        },
        "audits.media.upload.comment": {
            "uri": "audits\/{audit}\/media\/comment",
            "methods": ["POST"],
            "parameters": ["audit"],
            "bindings": {"audit": "id"}
        },
        "audits.comments.store": {
            "uri": "audits\/{audit}\/comments",
            "methods": ["POST"],
            "parameters": ["audit"],
            "bindings": {"audit": "id"}
        },
        "audits.export.pdf": {
            "uri": "audits\/{audit}\/export-pdf",
            "methods": ["GET", "HEAD"],
            "parameters": ["audit"],
            "bindings": {"audit": "id"}
        },
        "scoring.index": {"uri": "scoring\/sheets", "methods": ["GET", "HEAD"]},
        "scoring.sheets.show": {
            "uri": "scoring\/sheets\/{sheet}",
            "methods": ["GET", "HEAD"],
            "parameters": ["sheet"],
            "bindings": {"sheet": "id"}
        },
        "scoring.sheets.confirm": {
            "uri": "scoring\/sheets\/{sheet}\/confirm",
            "methods": ["POST"],
            "parameters": ["sheet"],
            "bindings": {"sheet": "id"}
        },
        "scoring.requests.store": {
            "uri": "scoring\/sheets\/{sheet}\/requests",
            "methods": ["POST"],
            "parameters": ["sheet"],
            "bindings": {"sheet": "id"}
        },
        "scoring.requests.update": {
            "uri": "scoring\/requests\/{request}",
            "methods": ["PUT"],
            "parameters": ["request"]
        },
        "scoring.requests.destroy": {
            "uri": "scoring\/requests\/{request}",
            "methods": ["DELETE"],
            "parameters": ["request"]
        },
        "scoring.variants.update": {
            "uri": "scoring\/variants\/{variant}",
            "methods": ["PUT"],
            "parameters": ["variant"],
            "bindings": {"variant": "id"}
        },
        "scoring.variants.destroy": {
            "uri": "scoring\/variants\/{variant}",
            "methods": ["DELETE"],
            "parameters": ["variant"],
            "bindings": {"variant": "id"}
        },
        "scoring.entries.create": {
            "uri": "scoring\/sheets\/{sheet}\/entries\/create",
            "methods": ["GET", "HEAD"],
            "parameters": ["sheet"],
            "bindings": {"sheet": "id"}
        },
        "scoring.entries.store": {
            "uri": "scoring\/sheets\/{sheet}\/entries",
            "methods": ["POST"],
            "parameters": ["sheet"],
            "bindings": {"sheet": "id"}
        },
        "scoring.entries.destroy": {"uri": "scoring\/entries\/{entry}", "methods": ["DELETE"], "parameters": ["entry"]},
        "scoring.summary": {"uri": "scoring\/summary", "methods": ["GET", "HEAD"]},
        "scoring.export.summary": {"uri": "scoring\/export\/summary", "methods": ["GET", "HEAD"]},
        "scoring.export.sheet": {
            "uri": "scoring\/export\/sheet\/{sheet}",
            "methods": ["GET", "HEAD"],
            "parameters": ["sheet"]
        },
        "admin.users": {"uri": "admin\/users", "methods": ["GET", "HEAD"]},
        "admin.users.show": {"uri": "admin\/users\/{user}", "methods": ["GET", "HEAD"], "parameters": ["user"]},
        "admin.users.update": {"uri": "admin\/users\/{user}", "methods": ["PUT"], "parameters": ["user"]},
        "admin.users.destroy": {"uri": "admin\/users\/{user}", "methods": ["DELETE"], "parameters": ["user"]},
        "admin.users.approve": {"uri": "admin\/users\/{user}\/approve", "methods": ["POST"], "parameters": ["user"]},
        "admin.users.assign-role": {
            "uri": "admin\/users\/{user}\/assign-role",
            "methods": ["POST"],
            "parameters": ["user"]
        },
        "admin.users.bulk-approve": {"uri": "admin\/users\/bulk-approve", "methods": ["POST"]},
        "admin.users.bulk-destroy": {"uri": "admin\/users\/bulk-destroy", "methods": ["DELETE"]},
        "admin.users.leaders": {"uri": "admin\/leaders", "methods": ["GET", "HEAD"]},
        "admin.statistics": {"uri": "admin\/statistics", "methods": ["GET", "HEAD"]},
        "admin.departments.index": {"uri": "admin\/departments", "methods": ["GET", "HEAD"]},
        "admin.departments.create": {"uri": "admin\/departments\/create", "methods": ["GET", "HEAD"]},
        "admin.departments.store": {"uri": "admin\/departments", "methods": ["POST"]},
        "admin.departments.edit": {
            "uri": "admin\/departments\/{department}\/edit",
            "methods": ["GET", "HEAD"],
            "parameters": ["department"]
        },
        "admin.departments.update": {
            "uri": "admin\/departments\/{department}",
            "methods": ["PUT", "PATCH"],
            "parameters": ["department"],
            "bindings": {"department": "id"}
        },
        "admin.departments.destroy": {
            "uri": "admin\/departments\/{department}",
            "methods": ["DELETE"],
            "parameters": ["department"],
            "bindings": {"department": "id"}
        },
        "admin.positions.index": {"uri": "admin\/positions", "methods": ["GET", "HEAD"]},
        "admin.positions.create": {"uri": "admin\/positions\/create", "methods": ["GET", "HEAD"]},
        "admin.positions.store": {"uri": "admin\/positions", "methods": ["POST"]},
        "admin.positions.edit": {
            "uri": "admin\/positions\/{position}\/edit",
            "methods": ["GET", "HEAD"],
            "parameters": ["position"]
        },
        "admin.positions.update": {
            "uri": "admin\/positions\/{position}",
            "methods": ["PUT", "PATCH"],
            "parameters": ["position"],
            "bindings": {"position": "id"}
        },
        "admin.positions.destroy": {
            "uri": "admin\/positions\/{position}",
            "methods": ["DELETE"],
            "parameters": ["position"],
            "bindings": {"position": "id"}
        },
        "admin.positions.by-department": {
            "uri": "admin\/positions\/by-department\/{departmentId}",
            "methods": ["GET", "HEAD"],
            "parameters": ["departmentId"]
        },
        "admin.scoring.categories": {"uri": "admin\/scoring\/categories", "methods": ["GET", "HEAD"]},
        "admin.scoring.categories.store": {"uri": "admin\/scoring\/categories", "methods": ["POST"]},
        "admin.scoring.categories.update": {
            "uri": "admin\/scoring\/categories\/{category}",
            "methods": ["PUT"],
            "parameters": ["category"],
            "bindings": {"category": "id"}
        },
        "admin.scoring.categories.destroy": {
            "uri": "admin\/scoring\/categories\/{category}",
            "methods": ["DELETE"],
            "parameters": ["category"],
            "bindings": {"category": "id"}
        },
        "admin.scoring.categories.reorder": {"uri": "admin\/scoring\/categories\/reorder", "methods": ["POST"]},
        "admin.managers.index": {"uri": "admin\/managers", "methods": ["GET", "HEAD"]},
        "admin.managers.create": {"uri": "admin\/managers\/create", "methods": ["GET", "HEAD"]},
        "admin.managers.store": {"uri": "admin\/managers", "methods": ["POST"]},
        "admin.managers.edit": {
            "uri": "admin\/managers\/{manager}\/edit",
            "methods": ["GET", "HEAD"],
            "parameters": ["manager"]
        },
        "admin.managers.update": {
            "uri": "admin\/managers\/{manager}",
            "methods": ["PUT", "PATCH"],
            "parameters": ["manager"],
            "bindings": {"manager": "id"}
        },
        "admin.managers.destroy": {
            "uri": "admin\/managers\/{manager}",
            "methods": ["DELETE"],
            "parameters": ["manager"],
            "bindings": {"manager": "id"}
        },
        "storage.local": {
            "uri": "storage\/{path}",
            "methods": ["GET", "HEAD"],
            "wheres": {"path": ".*"},
            "parameters": ["path"]
        },
        "storage.local.upload": {
            "uri": "storage\/{path}",
            "methods": ["PUT"],
            "wheres": {"path": ".*"},
            "parameters": ["path"]
        }
    }
};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
    Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export {Ziggy};
