
//转换从后台获取的对象集合
export default {
 resolveObjEvent:(data)=>{
    let OldObj = data
    let NewObjArray = []
    for(let i in OldObj){
        NewObjArray.push(OldObj[i])
    }
    return NewObjArray
  }
}
