import React, { Component } from 'react'
import { Button,Breadcrumb } from 'antd'
import OpusTabs from './opusTabs/OpusTabs'
import UploadOpus from './uploadOpus/UploadOpus'

class Opus extends Component {
  constructor(props){
    super(props)
    this.state={
      renderNow:false,
    }
    // console.log(this.props);
  }

  handleChangeRomance=()=>{
    const { location } = this.props;
    const { pathname } = location;
    // console.log(pathname,'pathname');
    const pathList = pathname.split('/');
    // console.log(pathList,'11');
    const newPathName = pathList[pathList.length - 1]
    if(newPathName !== 'opus'){
      this.setState({
        renderNow:true
      })
    }else{
      this.setState({
        renderNow:false
      })
    }
  }

  render(){
    return(
      <div>
        {
          this.state.renderNow !== true? <OpusTabs /> : <UploadOpus />
        }
      </div>
    )
  }
  componentDidMount(){
    this.handleChangeRomance()
  }
  componentWillReceiveProps(nextProps){
    if(this.props.location.pathname!== nextProps.location.pathname){
      this.props.location.pathname = nextProps.location.pathname
      this.handleChangeRomance()
    }
  }
}

export default Opus
