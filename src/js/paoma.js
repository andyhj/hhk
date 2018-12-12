
window.onload = function(){
    var paomadeng = {
        currentIndex : 1, //当前索引
        indexCount : 16, //个数
        timer : 0, //定时器
        currentCycle : 0, //当前圈数
        cycles : 4, //跑的圈数
        speed : 400, //速度，即定时器的时间间隔
        key : 0, //钥匙,随机数
        btn : 0, //触发按钮
        classPrefix : "pao-", //元素类名class前缀
        
        reset : function(){
            //触发对象
            paomadeng.btn = this;            
            clearInterval(paomadeng.timer);
            paomadeng.currentCycle = 0;
            paomadeng.speed = 400;
            paomadeng.key = Math.ceil(Math.random() * paomadeng.indexCount);
            console.log("key:" + paomadeng.key);            
            paomadeng.run();
        },
        run : function(){
            console.log("speed:" + paomadeng.speed);           
            var before = paomadeng.currentIndex == 1 ? paomadeng.indexCount : paomadeng.currentIndex - 1;
            
            //设置上一索引的类名
            var beforeNode = document.getElementsByClassName(paomadeng.classPrefix + before)[0];
            var beforeClassNewName = beforeNode.className.replace("after","");
            beforeNode.className = beforeClassNewName;
            //设置当前索引的类名
            var currentNode = document.getElementsByClassName(paomadeng.classPrefix + paomadeng.currentIndex)[0];
            currentNode.className += " on"; //注意前面有空格          
            paomadeng.upSpeed();
            paomadeng.downSpeed();
            
            paomadeng.currentIndex += 1;
            paomadeng.currentIndex = paomadeng.currentIndex > paomadeng.indexCount ? 1: paomadeng.currentIndex;        
        },
        //加速
        upSpeed : function(){
            //前2圈且speed>100时加速
            if(paomadeng.currentCycle < 2 && paomadeng.speed > 100){
                paomadeng.speed -= 5 * paomadeng.currentIndex ;
                paomadeng.stop();
                paomadeng.start();
            }
        },
        //增加圈数 并 减速
        downSpeed : function(){
            //增加圈数
            if(paomadeng.currentIndex == paomadeng.indexCount){
                paomadeng.currentCycle += 1;
            }
            
            //如果当前所跑圈数小于总圈数-1 并且 速度小于400，那么减速
            if(paomadeng.currentCycle > paomadeng.cycles-1 && paomadeng.speed < 400){
                paomadeng.speed += 20;
                paomadeng.stop();
                paomadeng.start();
            }
            
            //如果当前所跑圈数大于总圈数 且 索引值等于key，那么停止奔跑
            if(paomadeng.currentCycle > paomadeng.cycles && paomadeng.currentIndex == paomadeng.key){
                paomadeng.stop();
                paomadeng.showPrize();
            }
        },
        stop : function(){
            clearInterval(paomadeng.timer);
        },
        start : function(){
            paomadeng.timer = setInterval(paomadeng.run , paomadeng.speed);
        },
        showPrize : function(){
            //过一会再显示提示信息
            setTimeout(function(){
                alert ("恭喜，你中了" + paomadeng.key + "等奖");
                paomadeng.btn.style.display = "block";
            },700);

        }
    };
    document.getElementById("startPao").onclick= paomadeng.reset;
};
