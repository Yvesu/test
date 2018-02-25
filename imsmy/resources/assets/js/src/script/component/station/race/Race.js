import React, { Component } from 'react'



//竞赛
class Race extends Component {
  constructor(props){
    super(props)
    this.state={
      test:[]
    }
  }
  add(){
    let test= this.state.test;
    test.push({})
    this.setState({
      test:test
    })
    // console.log(test);
    // console.log(test.length);
  }

  delete(index){
    //删除的时候老是删除最后一个
    // console.log(index===0);
    if(index === 0){
      return false
    }else{
      let test=this.state.test
      test.splice(index,1)
      console.log(test);
      this.setState({
        test:test
      })
    }

    //  var index = e.target.getAttribute("data-index")
    //  console.log(index);
    //  var test = this.state.test;
    //  test.pop();
    // // $(test).remove(test[index])
    // // console.log($(this));
    //   console.log(test.length);
    //  this.setState({test:test})
  }


  render(){

    return(
      <div style={{padding:"10px 15px",border:"1px solid #000",minHeight:"500px",background:"#fff"}}>
        <ul>
          test
          {
            this.state.test.map((item,index)=>{
              return(
                <li key={index} style={{border:"1px solid #000",margin:"10px"}}>
                  {index} :
                  这是第{index + 1}个
                  <button onClick={this.delete.bind(this)}>删除</button>
                </li>
              )
            })
          }
        </ul>
        <button onClick={this.add.bind(this)}>添加</button>
      </div>
    )
  }
componentDidMount(){
  this.add()

}
// componentWillReceiveProps(nextProps){
//   this.state.test
// }

}

export default Race
