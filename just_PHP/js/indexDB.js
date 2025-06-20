var db;
var request = indexedDB.open("CodingOnline", 1); // "MyDatabase" 是数据库名称，"1" 是版本号
 
request.onerror = function(event) {
    console.error("Database error: " + event.target.errorCode);
};
 
request.onsuccess = function(event) {
    db = event.target.result;
    console.log("Database opened successfully");
};
 
request.onupgradeneeded = function(event) {
    var db = event.target.result;
    // 创建对象存储（如果尚未存在）
    if (!db.objectStoreNames.contains("myStore")) {
        db.createObjectStore("myStore", { keyPath: "id" }); // "myStore" 是对象存储的名称，"id" 是主键字段
    }
};

function addData() {
    var transaction = db.transaction(["myStore"], "readwrite"); // 指定要操作的对象存储和模式（只读或读写）
    var store = transaction.objectStore("myStore"); // 获取对象存储的引用
    
    var request = store.add({ id: 1, name: "John Doe", age: 30 }); // 添加数据到对象存储中
    request.onsuccess = function(event) {
        console.log("Data written to database");
    };
    request.onerror = function(event) {
        console.error("Unable to write data");
    };
}

function readData() {
    var transaction = db.transaction(["myStore"]); // 指定要操作的对象存储和模式（只读）
    var store = transaction.objectStore("myStore"); // 获取对象存储的引用
    var request = store.get(1); // 通过主键获取数据，这里假设我们通过id来获取数据
    request.onsuccess = function(event) {
        if (request.result) {
            console.log("Data retrieved:", request.result); // 输出获取的数据
        } else {
            console.log("No data found"); // 如果没有找到数据则输出此信息
        }
    };
    request.onerror = function(event) {
        console.error("Unable to read data"); // 读取数据失败时输出错误信息
    };
}

function updateData() {
    var transaction = db.transaction(["myStore"], "readwrite"); // 指定要操作的对象存储和模式（读写）
    var store = transaction.objectStore("myStore"); // 获取对象存储的引用
    var request = store.put({ id: 1, name: "Jane Doe", age: 25 }); // 更新数据到对象存储中，这里假设id是1的数据将被更新
    request.onsuccess = function(event) {
        console.log("Data updated in database"); // 数据更新成功时输出此信息
    };
    request.onerror = function(event) {
        console.error("Unable to update data"); // 数据更新失败时输出错误信息
    };
}

function deleteData() {
    var transaction = db.transaction(["myStore"], "readwrite"); // 指定要操作的对象存储和模式（读写）
    var store = transaction.objectStore("myStore"); // 获取对象存储的引用
    var request = store.delete(1); // 通过主键删除数据，

}

function getCount()
{
   var transaction = db.transaction(["myStore"], "readwrite"); // 指定要操作的对象存储和模式（读写）
    
   var store = transaction.objectStore("myStore"); // 获取对象存储的引用
   store.count().onsuccess = function(event) {

      console.log('Total entries:', event.target.result);
    
   };
}