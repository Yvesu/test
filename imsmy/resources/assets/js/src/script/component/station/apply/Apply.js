import React, { Component } from 'react'

//提现申请
class Apply extends Component {
  constructor(props){
    super(props)
    this.state={
      data:''
    }
  }
  tryTest(){

    var test = 'http://ects.cdn.hivideo.com/fragment/zip/1000240/63/20171121094956.zip'
    var dt = /http:(\S*).com[^\s]/


    var cc = test.replace(dt,'')
    // var cc2 = cc.replace('//','')
    this.setState({
      data:cc
    })
    // var dtt = cc.replace(dt,'')
    // var dtc = dtt.replace(dt,'')
     console.log(cc);
     // console.log(cc2);
     // console.log(dtt);
     // console.log(dtc);

  }
  render(){
    return(
      <div style={{background:"#fff"}}>
        <p style={{height:30,border:'1px solid #000'}} >{this.state.data}</p>
        <button onClick={this.tryTest.bind(this)}>test</button>
      </div>
    )
  }
}

export default Apply
